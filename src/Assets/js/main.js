let deleteButtons = document.querySelectorAll('[id^=delete-note]')
deleteButtons.forEach((btn) => {
    btn.addEventListener("click", function (event) {
        let hiddenNoteIdInputField = document.getElementById('note-id-to-delete')
        let noteId = this.id.match(/\d+$/)
        hiddenNoteIdInputField.value = noteId[0]
        let dialog = document.getElementById('my-dialog')
        dialog.classList.remove("hidden")
    });
});

// Close the note deletion confirmation dialog when the cancel button is pressed
let cancelDialog = document.getElementById('closeDialog')
cancelDialog.addEventListener("click", function (event) {
    let dialog = document.getElementById('my-dialog')
    dialog.classList.add("hidden")
});

// Close the note deletion confirmation dialog when the close button is pressed
let closeButton = document.getElementById('btnCloseDialog')
closeButton.addEventListener("click", function (event) {
    let dialog = document.getElementById('my-dialog')
    dialog.classList.add("hidden")
});
