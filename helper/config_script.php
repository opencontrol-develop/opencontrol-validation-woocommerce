<script>
    $(document).ready(function(){
        isSandbox();
        hiddeOrShow();
    });

    function isSandbox() {
        let $checkbox = $('#is_sandbox').click(function(){
            hiddeOrShow();
        });
    }

    function hiddeOrShow(){
        let checked = $('#is_sandbox').is(':checked');
        let inputsLive = "<?php echo Constants::SETTINGS_NAME.'[live_'?>";
        let inputsSandbox = "<?php echo Constants::SETTINGS_NAME.'[sandbox_'?>";
        if (checked){
            $("input[name*='"+inputsLive+"']").parent().parent().hide();
            $("input[name*='"+inputsSandbox+"']").parent().parent().show();
            $("input[name*='"+inputsSandbox+"']").prop('required', true);
            $("input[name*='"+inputsLive+"']").prop('required', false);
        } else {
            $("input[name*='"+inputsLive+"']").parent().parent().show();
            $("input[name*='"+inputsSandbox+"']").parent().parent().hide();
            $("input[name*='"+inputsSandbox+"']").prop('required', false);
            $("input[name*='"+inputsLive+"']").prop('required', true);
        }
    }
</script>