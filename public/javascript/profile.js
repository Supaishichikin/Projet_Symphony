$('.achievements').hide();

$(function() {
    let triggers = $(".see-achievements");
    triggers.click(function(){
        $(this).children().first().toggleClass('rotated');
        $(this).next().slideToggle(400);
    });
});