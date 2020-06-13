$(document).ready(function(){
    $('#testform').on('submit', function(e) {
        var emptyCount = 0;
        var missing='';
        $('.form-group').each(function() {
            var found = false;
            if(emptyCount==0) {
                $(this).find('input[type=radio]').each(function() {
                    if ($(this).prop('checked')) {
                        found = true;
                    }
                });
                if (!found) {
                    emptyCount++;
                    missing=$(this).attr('data-id');
                }
            }
        });
        if (emptyCount > 0) {
            $('#testform #qes'+missing).addClass('border border-danger p-3 rad-5');
            if(!$('#testform #qes'+missing+' > p').length) {
                $('#testform #qes'+missing).append('<p class="text-danger text-center">This field is required</p>');
            }
            var elementPosition = document.getElementById("qes"+missing).offsetTop;
            window.scrollTo({
              top: elementPosition - 10
            });
            $('#testform #qes'+missing).effect('shake');
            return false;
        }
        return true;
    });
});
function remValidate(id) {
    $('#testform #qes'+id).removeClass('border border-danger p-3 rad-5');
    $('#testform #qes'+id+' > p').remove();
}