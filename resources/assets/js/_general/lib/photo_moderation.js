$(function(){
    photos.init();
});

photos = {
    init() {
        $('#ratingType').unbind().bind('change', function(){
            photos.redirect($(this).val());
        });

        $('.moderationPhotoActions button').unbind().bind('click', function() {
            photos.rate($(this).attr('data-id'), $(this).attr('data-type'));
        });

        $('#rateAll button').unbind().bind('click', function() {
            let type = $(this).attr('data-type');
            swal({
                title: 'Are you sure?',
                text: 'You want to rate all photos as '+ (type.charAt(0).toUpperCase() + type.slice(1)),
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then(result => {
                if (result) photos.rateAll(type);
            });
        });
    },
    redirect(to = null) {
        let type = to ? to : $('#ratingType').val();

        if (type == 'all') window.location = Laravel.route;
        else window.location = Laravel.route + '?only=' + type;
    },
    rate(id, type) {
        makeAjaxRequest(Laravel.rate, {
                id: id,
                type: type,
            }, 'POST', true)
            .then(data => {
                if (data.success) {
                    showNotification(data.message);
                    photos.remove(data.id);
                    photos.counters(1);
                } else {
                    showErrorNotification(data.message);
                }
            });
    },
    rateAll(type) {
        let ids = [];
        $('.moderationPhoto').each(function() {
            ids.push(parseInt($(this).attr('data-id')));
        });

        makeAjaxRequest(Laravel.rateAll, {
                ids: ids,
                type: type,
            }, 'POST', true)
            .then(data => {
                if (data.success) {
                    showNotification(data.message);
                    photos.redirect();
                } else {
                    showErrorNotification(data.message);
                }
            });
    },
    remove(id) {
        $('.moderationPhoto[data-id="'+ id +'"]').remove();

        if ($('.moderationPhoto').length == 0) {
            photos.redirect();
        }
    },
    counters(count) {
        Laravel.rated   += count;
        Laravel.unrated -= count;

        $('#counterRated').html(Laravel.rated);
        $('#counterUnrated').html(Laravel.unrated);
    },
}
