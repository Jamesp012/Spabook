<script>
    function inputValidation(...args) {
        let isValidated = true;
        $.each(args, function(i, e) {
            let element = $(`#${e}`);
            let reqfield = ($(`label[for='${e}']`).text()).replace(/[^a-zA-Z0-9\s]/g, '');
            if (element.val().trim() == '' || element.val().trim() == '-') {
                invalidField(e, `${reqfield} is required.`);
                isValidated = false;
            } else {
                validField(e);
            }
        });
        return isValidated;
    }

    function invalidField(field, msg) {
        $('#' + field).addClass('is-invalid').removeClass('is-valid');
        $('#' + field).next().html(msg);
    }

    function validField(field) {
        $('#' + field).addClass('is-valid').removeClass('is-invalid');
        $('#' + field).next().html();
    }
</script>