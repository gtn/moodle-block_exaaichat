<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exaaichat;

require_once $CFG->dirroot . '/course/externallib.php';

/**
 * Helper class for handling callbacks and function definitions.
 *
 * This class provides methods to retrieve Moodle web service functions,
 * parse callback parameters, and call the appropriate functions.
 */
class callback_helper {
    /**
     * Get all AI function definitions.
     *
     * This method retrieves all available AI functions, including Moodle web service functions
     * and custom class methods defined in the actions class.
     *
     * @return array An array of function definitions.
     */
    public static function get_functions() {
        static $functions = null;

        if ($functions) {
            return $functions;
        }

        $functions = array_merge(
            static::get_moodle_ws_functions(\core_course_external::class, [
                'get_course_contents',
                // 'get_courses', // not needed, because this retuns all moodle courses! class action has own implementation
                'get_categories',
                'get_recent_courses',
            ]),

            // other possible functions:
            // static::get_moodle_ws_functions(\core_enrol_external::class),
            // static::get_moodle_ws_functions(\core_calendar_external::class),
            // but those need to be required manually:
            // require_once $CFG->dirroot . '/enrol/externallib.php';
            // require_once($CFG->dirroot . '/calendar/externallib.php');

            static::get_class_functions(actions::class)
        );

        // make each function name unique
        $function_names = [];
        foreach ($functions as &$function) {
            if (isset($function_names[$function['name']])) {
                for ($i = 1; $i < 10; $i++) {
                    if (!isset($function_names[$function['name'] . $i])) {
                        $function['name'] .= $i;
                        break;
                    }
                }
            }

            $function_names[$function['name']] = true;
        }

        return $functions;
    }

    /**
     * get the phpdoc description of a class method
     * @param string $className
     * @param string $methodName
     * @return string|null
     * @throws \ReflectionException
     */
    protected static function get_method_doc_description(string $className, string $methodName): ?string {
        $reflector = new \ReflectionMethod($className, $methodName);
        $docComment = $reflector->getDocComment();

        if ($docComment === false) {
            return null; // No docblock found
        }

        // Remove the opening and closing docblock tags
        $docComment = preg_replace('/^\/\*\*|\*\/$/', '', $docComment);

        // Split lines and trim each one
        $lines = array_map('trim', explode("\n", $docComment));

        $description = [];
        foreach ($lines as $line) {
            // Remove the leading asterisk (*) and any whitespace
            $line = preg_replace('/^\*\s?/', '', $line);

            // Stop collecting if an annotation is found
            if (strpos($line, '@') === 0) {
                break;
            }

            // Ignore empty lines
            if (!empty($line)) {
                $description[] = $line;
            }
        }

        // Return the description as a single string
        return implode("\n", $description);
    }

    /**
     * get the function parameters from a php function
     * @param $callback
     * @return array
     * @throws \ReflectionException
     */
    protected static function parse_callback_parameters($callback) {
        if ($callback instanceof \ReflectionMethod) {
            $reflection = $callback;
        } else {
            // Ensure the callback is valid
            if (!is_callable($callback)) {
                throw new \Exception('Provided callback is not callable.');
            }

            // Create a reflection of the function
            $reflection = new \ReflectionFunction($callback);
        }

        $parameters = [];
        $required = [];

        // Iterate over each parameter of the function
        foreach ($reflection->getParameters() as $param) {
            if (preg_match('!\*\s+@param\s+[^\s]+\s+\$' . $param->getName() . '\s+(.*)!', $reflection->getDocComment(), $matches)) {
                $description = $matches[1];
            } else {
                $description = $param->getName();
            }

            if ($param->isOptional()) {
                $description .= ' (default value: ' . gettype($param->getDefaultValue()) . ' ' . json_encode($param->getDefaultValue()) . ')';
            }

            $paramDetails = [
                'type' => 'string', // Default to string if type cannot be determined
                'description' => $description,
            ];

            // Check if the parameter has a type
            $paramType = $param->getType();
            if ($paramType instanceof \ReflectionNamedType) {
                $typeName = $paramType->getName();

                // Map PHP types to JSON schema types
                switch ($typeName) {
                    case 'int':
                    case 'float':
                        $paramDetails['type'] = 'number';
                        break;
                    case 'bool':
                        $paramDetails['type'] = 'boolean';
                        break;
                    case 'array':
                        $paramDetails['type'] = 'array';
                        $paramDetails['items'] = [
                            'type' => 'string', // TODO: actual type of array items
                        ];
                        break;
                    case 'object':
                        $paramDetails['type'] = 'object';
                        break;
                    case 'string':
                    default:
                        $paramDetails['type'] = 'string';
                        break;
                }
            }

            $parameters[$param->getName()] = $paramDetails;

            // Check if the parameter is required
            // if (!$param->isOptional()) {
            //     $required[] = $param->getName();
            // }

            // in strict mode: required to be supplied and to be an array including every key in properties. Missing 'courseid'.
            $required[] = $param->getName();
        }

        // Build the OpenAI API client schema
        $schema = [
            'type' => 'object',
            'properties' => (object)$parameters, // needs to be an object when parameters is empty
            'required' => $required,
            'additionalProperties' => false,
        ];

        return $schema;
    }

    /**
     * Call an AI function
     * @param object $function
     * @return bool|float|int|string
     * @throws \Exception
     */
    public static function call_tool(object $function) {
        logger::debug('api calling function: ' . $function->name);

        $functions = static::get_functions();
        $functionDefinition = current(array_filter($functions, fn($f) => $f['name'] == $function->name));
        if ($functionDefinition) {
            $arguments = $function->arguments ? json_decode($function->arguments) : [];
            if (is_object($arguments)) {
                $arguments = (array)$arguments;
            }

            logger::debug('arguments:', $arguments);

            $finalArguments = [];
            foreach ($functionDefinition['parameters']['properties'] as $paramName => $paramDetails) {
                if (isset($arguments[$paramName])) {
                    $finalArguments[] = $arguments[$paramName];
                } elseif (in_array($paramName, $functionDefinition['parameters']['required'])) {
                    throw new \Exception("Missing required parameter: $paramName");
                }
            }

            $callback = $functionDefinition['callback'];

            try {
                if (is_array($callback)) {
                    $output = $callback[0]::{$callback[1]}(...$finalArguments);
                } else {
                    $output = $callback(...$finalArguments);
                }
            } catch (\Exception $e) {
                $output = 'Fehler: ' . $e->getMessage();
            }
        } else {
            $output = 'This API is not available';
        }
        logger::debug("output:", $output);

        return is_scalar($output) ? $output : json_encode($output);
    }

    /**
     * get AI function definitions from a all static methods of a class
     * @param string $class
     * @return array
     * @throws \ReflectionException
     */
    protected static function get_class_functions(string $class): array {
        $functions = [];

        $reflection = new \ReflectionClass($class);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $methodName = $method->getName();

            $functions[] = [
                'name' => $methodName, // trim(substr(preg_replace('!.*\\\\!', '', $class) . '_' . $methodName, -64), '_'),
                'description' => static::get_method_doc_description($class, $methodName) ?: $methodName,
                'parameters' => static::parse_callback_parameters($method),
                // Setting strict to true will ensure function calls reliably adhere to the function schema, instead of being best effort. We recommend always enabling strict mode.
                // https://platform.openai.com/docs/guides/function-calling
                'strict' => true,

                'callback' => [$class, $methodName],
            ];
        }

        return $functions;
    }

    /**
     * Get AI function definitions from a moodle externallib webservice class
     * @param string $class
     * @param ?array $method_filter
     * @return array
     * @throws \ReflectionException
     */
    protected static function get_moodle_ws_functions(string $class, ?array $method_filter = null): array {
        $functions = [];

        $reflection = new \ReflectionClass($class);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $methodName = $method->getName();
            if ($methodName == '__construct') {
                continue;
            }
            if (str_ends_with($methodName, '_returns')) {
                continue;
            }
            if (str_ends_with($methodName, '_parameters')) {
                continue;
            }

            if (is_array($method_filter) && !in_array($methodName, $method_filter)) {
                continue;
            }

            if (!$reflection->hasMethod($methodName . '_returns')) {
                // nur ws funktionen zurückliefern
                // = alle, die ein *_returns() haben
                continue;
            }

            $functions[] = [
                'name' => $methodName, // trim(substr(preg_replace('!.*\\\\!', '', $class) . '_' . $methodName, -64), '_'),
                'description' => static::get_method_doc_description($class, $methodName) ?: $methodName,
                'parameters' => static::parse_callback_parameters($method),
                'callback' => [$class, $methodName],
            ];
        }

        return $functions;
    }
}
