<div class="video-modal" style="display: none">
    <div class="media-watching">
        <div class="header">
            <i id="close-videoModal" class="back"></i>
        </div>
        <div class="body">
            <div class="media">
                <div id="video" class="video-wrapper">
                    <video autoplay muted controls controlsList="nodownload">
                        <source type="video/mp4"/>
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .video-modal {
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(8.15485px);
        z-index: 1040;
        position: fixed !important;
        overflow: hidden !important;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
    }

    .media-watching {
        left: 0;
        right: 0;
        bottom: 0;
        top: 0;
        position: fixed;
        width: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(21.7463px);
        display: flex;
        flex-flow: column nowrap;
        justify-content: center;
        align-items: center;
    }

    .header {
        width: 100%;
    }

    .back {
        display: flex;
        position: absolute;
        top: 30px;
        right: 30px;
        left: auto;
        width: 50px;
        height: 50px;
        cursor: pointer;
    }

    .back:before {
        width: 30px;
        height: 30px;
        background-image: url(/main/img/icons/message-removed.svg);
        filter: brightness(1000);
        content: "";
        position: absolute;
        background-size: contain;
        background-position: center;
        background-repeat: no-repeat;
    }

    .body {
        /*padding: 45px 57px 40px 45px;*/
        width: 65%;
        height: 100%;
        margin: 0 auto;
        display: flex;
        align-items: center;
        overflow: hidden;
        box-sizing: border-box;
    }

    .media {
        height: 100%;
        width: 100%;
        max-height: -webkit-fill-available;
        display: flex;
        flex-flow: row nowrap;
        justify-content: center;
        align-items: center;
    }

    .video-wrapper {
        width: 100%;
        height: auto;
        max-height: 100%;
        display: flex;
        justify-content: center;
        align-content: center;
    }

    .video-wrapper > video {
        height: auto;
        width: 100%;
        vertical-align: middle;
        max-width: 100%;
        max-height: 100%;
        display: inline-block;
    }
</style>