$(function(){ // DOM ready
    $('#is-available').click(function(e) {
        e.preventDefault();

        let btn = $(this);
        let username = $('#registration_pseudo').val();
        $.get(
            (btn.attr('href').slice(0, -4) + username),
            function(response) {
                let availability = $('#availability');
                availability.text(response);
            }
        );
    })
});