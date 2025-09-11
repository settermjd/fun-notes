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

let cancelDialog = document.getElementById('closeDialog')
cancelDialog.addEventListener("click", function (event) {
    let dialog = document.getElementById('my-dialog')
    dialog.classList.add("hidden")
});
