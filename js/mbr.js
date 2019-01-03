$(document).ready(function(){
    $('input[type="checkbox"]').click(function(){
        var inputValue = $(this).attr("value");
        $("." + inputValue).toggle();
    });

    $('input[type="checkbox"]').on('click', function(){
        var data = {};
        data.id = $(this).attr('id');
        data.value = $(this).is(':checked') ? 1 : 0;
        active_function = $('input[id=mbr_active_function]').val();
        inactive_function = $('input[id=mbr_inactive_function]').val();
        if (data.value == 1) {
            active_function = +active_function + 1;
            inactive_function = +inactive_function - 1;
        } else {
            active_function = +active_function - 1;
            inactive_function = +inactive_function + 1;
                }

        $('input[id=mbr_active_function]').val(active_function);
        $('input[id=mbr_inactive_function]').val(inactive_function);

//       console.log(data);

        $.ajax({
            type: "POST",
            url: "/wp-content/plugins/mbr-function-control/includes/on-off.php",
            data: data,
        }).done(function(data) {
//      console.log(data);
        });
    });

    $('.mbr_open_accordion').click(function() {
        var data = {};
        data.functionid = $(this).attr('id');
        data.functionid = data.functionid.slice(13);
        console.log(data.functionid);

        $.ajax({
            type: "POST",
            url: "/wp-content/plugins/mbr-function-control/includes/on-off.php",
            data: data,
        }).done(function(data) {
//      console.log(functionid);
        });
    });

    $(document).on('click', '.showmodal', function() {
        var functionid = $(this).data('functionid');
        var functiontitle = $(this).data('functiontitle');
        var functioncontent = $(this).data('functioncontent');
        $('#ask_delete_file').html(functiontitle);
        $('#ask_delete_file2').html(functioncontent);
        $('input[name=function_id]').val(functionid);
        $('input[name=function_title]').val(functiontitle);
    });
});
