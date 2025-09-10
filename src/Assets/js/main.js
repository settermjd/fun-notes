import A11yDialog from 'a11y-dialog'

let dialogEl = document.getElementById('my-dialog')
let dialog = new A11yDialog(dialogEl)

let createNoteButton = document.getElementById('create-note')
createNoteButton.addEventListener('click', function (event) {
    console.log("Create note button was clicked, then something happened to the file.")
})

let deleteButtons = document.querySelectorAll('[id^=delete-note]')
deleteButtons.forEach((btn) => {
    btn.addEventListener("click", function (event) {
        console.log(this.id);
        console.log(event.target);
    });
});

dialog.on('show', function (event) {
    const container = event.target
    const target = event.detail.target
    const opener = target.closest('[data-a11y-dialog-show]')

    console.log(container, target, opener)
})
