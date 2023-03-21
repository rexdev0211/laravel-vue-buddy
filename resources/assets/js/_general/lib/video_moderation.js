$(function () {
    videos.init()
})

videos = {
    init() {
        $('.moderationVideoActions button').unbind().bind('click', function() {
            videos.rate($(this).attr('data-id'), $(this).attr('data-type'), $(this).attr('data-rating'), $(this).parent().attr('data-filter'));
        });

        $('.moderationVideoImage').bind('click', function () {
            videos.showVideoModal($(this).attr('data-url'));
        })

        $('#rateAll button').unbind().bind('click', function() {
            let type = $(this).attr('data-type');
            swal({
                title: 'Are you sure?',
                text: 'You want to rate all videos as '+ (type.charAt(0).toUpperCase() + type.slice(1)),
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then(result => {
                if (result) videos.rateAll(type);
            });
        });
    },
    showVideoModal(videoUrl) {
        $('body').css({overflow: "hidden"})
        $('#video video source').attr('src', videoUrl)
        $('.video-modal').css({display: "block"});
        $('#video video')[0].load();
        $('#close-videoModal').unbind().bind('click', function () {
            videos.hideVideoModal();
        })
    },
    hideVideoModal() {
        $('.video-modal').css({display: "none"});
        $('body').css({overflow: ""})
        $("#close-videoModal").unbind();
    },
    redirect(to = null) {
        let type = to ? to : $('#ratingType').val();

        if (type == 'unrated') window.location = Laravel.route;
        else window.location = Laravel.route + '?only=' + type;
    },
    rate(id, type, rating, currentFilter) {
        makeAjaxRequest(Laravel.rate, {
            id: id,
            type: type,
        }, 'POST', true)
            .then(data => {
                if (data.success) {
                    showNotification(data.message);
                    videos.remove(data.id);
                    videos.counters(1, rating, currentFilter);
                } else {
                    showErrorNotification(data.message);
                }
            });
    },
    rateAll(type) {
        let ids = [];
        $('.moderationVideo').each(function() {
            ids.push(parseInt($(this).attr('data-id')));
        });

        makeAjaxRequest(Laravel.rateAll, {
            ids: ids,
            type: type,
        }, 'POST', true)
            .then(data => {
                if (data.success) {
                    showNotification(data.message);
                    videos.redirect();
                } else {
                    showErrorNotification(data.message);
                }
            });
    },
    remove(id) {
        $('.moderationVideo[data-id="'+ id +'"]').remove();

        if ($('.moderationVideo').length == 0) {
            videos.redirect('unrated');
        }
    },
    counters(count, rating, currentFilter) {
        if (currentFilter === 'all' && rating === 'unrated' || currentFilter === 'unrated' || currentFilter === '') {
            Laravel.rated   += count;
            Laravel.unrated -= count;
        }

        $('#counterRated').html(Laravel.rated);
        $('#counterUnrated').html(Laravel.unrated);
    },
}
