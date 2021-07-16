<template>
  <div id="loader" :hidden="!isVisible">
    <div class="loader-text">
      <span v-html="text"></span>
    </div>
    <div class="spinner"></div>
  </div>
</template>

<script>
import "../../../../scss/spec/components/loader.scss"

export default {
  data(){
    return {
      isVisible : false,
      text      : "",
    }
  },
  methods: {
    /**
     * @description will show the loader
     */
    show(){
      this.isVisible = true;
    },
    /**
     * @description will hide the loader
     */
    hide(){
      this.isVisible = false;
    },
    /**
     * @description will set the text to be shown
     */
    setText(text){
      this.text = text;
    },
    /**
     * @description will remove the text from loader
     */
    clearText(){
      this.text = "";
    }
  }
}
</script>

<style scoped>
.loader-text {
  position: absolute;
  bottom: calc(50% - 60px);
  left: calc(50% - 100px);
}

#loader {
  width: 100%;
  height: 100%;
  position: absolute;
}
</style>