/* eslint-disable */
export const init = () => {
    document.querySelector('#id_s_block_exaaichat_type')?.addEventListener('change', e => {
        // If the API Type is changed, programmatically hit save so the page automatically reloads with the new options
        document.querySelector('.settingsform').classList.add('block_exaaichat')
        document.querySelector('.settingsform').classList.add('disabled')
        document.querySelector('.settingsform button[type="submit"]').click()
    })
}
