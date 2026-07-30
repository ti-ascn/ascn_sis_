function blockForm() {
    // obtener el formulario y el boton de envío
    const form = document.getElementById("form");
    const submitBtn = document.getElementById("submit-btn");

    // agregar un evento "submit" al formulario
    form.addEventListener("submit", function (event) {
        // obtener todos los campos obligatorios del formulario
        const requiredFields = document.querySelectorAll("[required]");

        // verificar si todos los campos obligatorios estan completos
        const isFormValid = Array.from(requiredFields).every(
            (field) => field.value.trim() !== ""
        );

        if (!isFormValid) {
            // si no estan completos, prevenir el envio del formulario y mostrar un mensaje de error
            event.preventDefault();
            // alert(
            //   "Por favor complete todos los campos obligatorios antes de enviar el formulario"
            // );
        } else {
            // si estan completos se deshabilita el boton de envío
            submitBtn.disabled = true;
            submitBtn.innerHTML = `Ya has usado este boton`;
        }
    });
}
// se usa con:
// onclick="blockForm()"
// agregar al boton que se va a usar el id:
// id="submit-btn"
// agregar al formulario el id:
// id="form"

// id="submit-btn" onclick="blockForm()
// id="form"
