class Countdown_cls {
    constructor(el) {
        this.el = jQuery(el);
        this.start();
    }
    start() {
        moment.tz.setDefault(this.el.data('offset'));
        let _timer = moment(this.el.data('date'), 'YYYY/MM/DD HH:mm:ss').tz(this.el.data('offset'));
        let _timezone = moment.tz.guess();
        let _countdown = _timer.clone().tz(_timezone).format('YYYY/MM/DD HH:mm:ss');
        this.el.countdown(_countdown).on('update.countdown', function(event) {
            jQuery(this).children('.days').children('.count').html(event.strftime('%D'));
            jQuery(this).children('.hours').children('.count').html(event.strftime('%H'));
            jQuery(this).children('.minutes').children('.count').html(event.strftime('%M'));
            jQuery(this).children('.seconds').children('.count').html(event.strftime('%S'));
        }).on('finish.countdown', function(event) {
            jQuery(this).children('.days').children('.count').html('0');
            jQuery(this).children('.hours').children('.count').html('0');
            jQuery(this).children('.minutes').children('.count').html('0');
            jQuery(this).children('.seconds').children('.count').html('0');
        });
    }
}

jQuery(document).ready(function() {
    jQuery('.as-countdown').each(function() {
        new Countdown_cls(this);
    });
});