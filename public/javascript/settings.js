$(function(){
    $('.editField').click(function(){

        $(this).parent().prev().children('.currentField').toggleClass('d-none');
        $(this).parent().prev().children('.newField').toggleClass('d-none');
    });
});