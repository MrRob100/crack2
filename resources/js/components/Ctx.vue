<template>
    <div v-if="tunes">
        <div class="switcher row to-blur">
            <div>
                <label class="switch">
                    <input v-model="playlist" type="checkbox">
                    <span class="slider"></span>
                </label>
            </div>
            <div class="col-5">
                <p v-if="playlist" class="pr">Repeat / <strong>Playlist</strong></p>
                <p v-else class="pr"><strong>Repeat</strong> / Playlist</p>
            </div>
        </div>

      <div class="slither"
           v-for="(tune, index) in tunesFormatted"
           :key="tune"
      >
        <tune
            :storage-path="storagePath"
            :img-path="imgPath"
            class="slither-class"
            :id="'slither-' + index"
            @ended="endHandler"
            @able="setPlayable"
            :playable="playable"
            :playlist="playlist"
            :ctx="ctx"
            :para="para"
            :name="tune"
            :pos="index"
            :run="run"
            :lastOne="tunesFormatted.length"
        ></tune>
      </div>

      <div class="control-box">

        <div class="crow ctop-row">
            <div class="close-container" v-for="(tune, index) in tunesFormatted" :key="tune">
                <i :id='"modal-close-"+index' class="fa fa-close modal-close" aria-hidden="true"></i>
            </div>

            <div class="fx-container speed-container">
                <input class="fx speed-control" type="range" min="0.5" max="1.5" step="0.01" value="1">
            </div>
            <div class="fx-container reverb-container">
                <input class="fx reverb-control" type="range" min="0" max="1" step="0.01" value="0">
            </div>
            <div class="fx-container filter-container">
                <input class="fx filter-control" type="range" min="0" max="20000" step="10" value="20000">
            </div>
            <div class="fx-container mod-container">
                <input class="fx mod-control" type="range" min="0" max="1" step="0.01" value="0">
            </div>
        </div>
        <div class="bottom-container">
            <div class="crow cbottom-row">
                    <span class="font-weight-bold speed-value">0</span>
                    <span class="font-weight-bold reverb-value">0</span>
                    <span class="font-weight-bold filter-value">0</span>
                    <span class="font-weight-bold mod-value">0</span>
            </div>
            <div class="crow cbottom-row">
                    <span class="font-weight-bold fx-label">Speed</span>
                    <span class="font-weight-bold fx-label">Reverb</span>
                    <span class="font-weight-bold fx-label">Filter</span>
                    <span class="font-weight-bold fx-label">Modulation</span>
            </div>
        </div>
        <div class="crow cstop" v-for="(tune, index) in tunesFormatted" :key="tune">
            <button class="font-weight-bold" :id='"stbutton-"+index'>Stop</button>
        </div>

      </div>

    </div>
</template>

<script>

export default {

    props: ["tunes", "para", "img-path", "storage-path"],

    data: function() {
        return {
            playlist: false,
            init: true,
            initSource: {},
            tunesFormatted: {},
            playable: true,
            run: false,
        }
    },

    mounted() {
        var isso = this;
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        const audioCtx = new AudioContext();
        this.ctx = audioCtx;
        this.tunesFormatted = this.tunes.split(" ");

        const loopUrl = this.storagePath + "tenniscourt.wav";

        const source = audioCtx.createBufferSource();

        var request = new XMLHttpRequest();
        request.open('GET', loopUrl, true);
        request.responseType = 'arraybuffer';

        request.onload = function() {
            var audioData = request.response;

            audioCtx.decodeAudioData(audioData, function(buffer) {
                var myBuffer = buffer;
                source.buffer = myBuffer;
                source.connect(audioCtx.destination);
                isso.initSource = source;

            },
            function (e) {
                "Error decoding audio data"
            });
        }

        request.send();

    },

    methods: {
        setPlayable(playable) {
            this.playable = playable;

            if (!playable && this.init) {
                this.init = false;
                this.initSource.start(0, 1);
            }
        },

        endHandler(val) {
            var isso = this;
            if (this.playlist) {
                isso.playable = true;

                // check if not last tune
                this.run = val + 1;
            }
        }

    }

}

</script>
<style>

.o-page {
    display: inline !important;
    color: #B27FFF;
    text-decoration: underline;
}

.delete-button {
    cursor: pointer;
    display: none;
}

.false-shift {
    margin-top: 10px;
    /* margin-left: 200px; */
}

.playback {
    clear: left;
}

.playback-item {
    display: inline;
    font-size: 18px;
}

html, body {
    height: 100%;
    overflow-x: hidden;
    /* touch-action: none; */
    background-color: rgb(50, 2, 95) !important;
    font-family: 'Courier New', Courier, monospace !important;
    font-weight: 500 !important;
    /* Disables pull-to-refresh but allows overscroll glow effects. */
    overscroll-behavior-y: contain;
}
body {
    width: 100%;
    position: relative;
    color: #B27FFF !important;
}

.navbar {
    height: 20px;
}

.info-icon {
    float: right;
    font-size: 24px;
    padding-right: 1%;
}

.stack-del button {
    display: none;
}

.upl {
    margin-left: 10px;
    width: 200px;
    float: left;
}

.dl-icon {
    filter: brightness(85%);
    position: absolute;
    right: 0;
    width: 40px;
    top: 0px;
}

.dld {
    background-color: rgba(0,0,0,0);
    border: none;
    position: absolute;
    right: 0;
}

.stack-house {
    width: 100%;
    margin-bottom: 10px;
    cursor: pointer;
    background-color: rgb(110, 78, 158);
}

.stack-slice:hover {
    cursor: pointer;
    filter: brightness(110%);
}

.contr {
    height: 50px;
}

.modal-close {
    cursor: pointer;
    font-size: 24px;
    position: absolute;
    right: 0;
}

.control-box {
    z-index: 10;
    display: none;
    overflow: hidden;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

@media (max-width: 600px) {
    .control-box {
        width: 100%;
        transform: translate(-50%, 0);
        bottom: 48px;
        top: initial;
    }
}

@media (min-width: 601px) {
    .control-box {
        width: 400px;
    }
}

.bottom-container {
    background-color: #111;
}

.close-container i {
    display: none;
}

.crow button {
    display: none;
    width: 100%;
}

.ctop-row {
    display: flex;
    height: 300px;
}

.cbottom-row {
    padding: 5px;
    display: flex;
}

.stack-bottom {
    height: 40px;
    background-color: rgb(79, 56, 114);
}

.fx-container {
    flex: 1;
    padding-left: 13.5%;
    padding-top: 270px;
}

.inln-btn {
    z-index: 1;
    position: absolute;
}

.canv {
    position: absolute;
    width: 100%;
    height: 40px;
}

.fx {
    width: 280px;
    transform: rotate(-90deg);
    transform-origin: 0%;
    position: absolute;
}

.cbottom-row span {
    text-align: center;
    flex: 1;
}

.stbutton {
    width: 100%;
}

.cstop button{
    border: none;
    height: 40px;
    background-color: #fff;
}
/* sliders */

/* Hides the slider so that custom slider can be made */
/* Otherwise white in Chrome */

input[type=range] {
  -webkit-appearance: none;
  background: transparent;
}

input[type=range]::-webkit-slider-thumb {
  -webkit-appearance: none;
}

input[type=range]:focus {
  outline: none; /* Removes the blue border. You should probably do some kind of focus styling for accessibility reasons though. */
}

input[type=range]::-ms-track {
  width: 100%;
  cursor: pointer;

  /* Hides the slider so custom styles can be added */
  background: transparent;
  border-color: transparent;
  color: transparent;
}

/* thumb */
input[type=range]::-webkit-slider-thumb {
  -webkit-appearance: none;
  height: 70px;
  width: 70px;
  border-radius: 50%;
  background: #ffffff;
  /* background: rgb(110, 78, 158); */
  cursor: pointer;
  margin-top: -14px; /* You need to specify a margin in Chrome, but in Firefox and IE it is automatic */
  box-shadow: -9px 12px 23px -3px rgba(0,0,0,0.59);

}

/* All the same stuff for Firefox */
input[type=range]::-moz-range-thumb {

  box-shadow: -9px 12px 23px -3px rgba(0,0,0,0.59);

  height: 70px;
  width: 70px;
  border-radius: 50%;
  background: #ffffff;
  /* background: rgb(110, 78, 158); */
  cursor: pointer;
}

/* All the same stuff for IE */
input[type=range]::-ms-thumb {
  box-shadow: -9px 12px 23px -3px rgba(0,0,0,0.59);

  height: 70px;
  width: 70px;
  border-radius: 50%;
  background: #ffffff;
  cursor: pointer;
}

/* slider */
/* The switch - the box around the slider */
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

/* Hide default HTML checkbox */
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

/* The slider */
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
}

input:checked + .slider {
    background-color: #1aa7b5;
}

input:focus + .slider {
    box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
    -webkit-transform: translateX(26px);
    -ms-transform: translateX(26px);
    transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}

.switcher {
    margin-left: 10px;
}

.pr {
    margin-top: 0.4rem;
}

.slither-class {
    width: 100%;
    top: calc(100% - 40px);
}

</style>
