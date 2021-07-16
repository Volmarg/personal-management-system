<template>
<div>

  <ul v-for="(data, index) in resultCheckData">
    <li class="text-dark">
      <b>
        {{ index }}:
        <ok v-if="data" />
        <fail v-else />
      </b>
    </li>
  </ul>

  <br/>

  <button class="btn btn-primary"
          ref="buttonNext"
          @click="goToPreviousStep"
          v-if="isBackButtonVisible"
  >Back</button>

  <button class="btn btn-primary"
          ref="buttonNext"
          @click="goToNextStep"
          v-if="isNextButtonVisible"
  >Next</button>

</div>
</template>

<script>
import axios             from "axios";
import FailMarkComponent from "./fail-mark.vue";
import OkMarkComponent   from "./ok-mark.vue";

export default {
  data(){
    return {
      loaderText           : "Checking environment...",
      isNextButtonVisible  : false,
      isBackButtonVisible  : true,
      stepName             : "Environment check",
      nextStepName         : "Configuration execution",
      previousStepName     : "Database",
      resultCheckData      : {},
      urls: {
        getEnvironmentCheckResultData: "/installer.php?GET_ENVIRONMENT_STATUS",
      }
    }
  },
  props: {
    performCheck : {
      type     : Boolean,
      required : false,
      default  : false,
    }
  },
  emits: [
    "step-finished",
    "step-cancelled",
    "step-mounted",
  ],
  components: {
    "fail" : FailMarkComponent,
    "ok"   : OkMarkComponent,
  },
  methods: {
    /**
     * @description will go to next step
     */
    goToNextStep(){
      this.$emit("step-finished", this.nextStepName);
    },
    /**
     * @description will go to previous step
     */
    goToPreviousStep(){
      this.$emit("step-cancelled", this.previousStepName);
    },
    /**
     * @description will return environment check result data
     */
    getEnvironmentCheckResultData(){
      let dataBag = this.$parent.$refs.stepDatabase.loadStepDataFromSession();

      this.$parent.$refs.loader.setText(this.loaderText);
      this.$parent.$refs.loader.show();

      axios.post(this.urls.getEnvironmentCheckResultData, dataBag).then( (response) => {
        this.$parent.$refs.loader.hide();
        this.$parent.$refs.loader.clearText();

        this.resultCheckData     = response.data.resultCheckData;
        this.isNextButtonVisible = response.data.success;

        if(!response.data.success){
          this.$emit("step-failed");
        }
      })
    }
  },
  mounted(){
    this.$emit("step-mounted", this.stepName);
  },
  watch: {
    performCheck(newValue){
      if(newValue){
        this.resultCheckData = {};
        this.getEnvironmentCheckResultData();
      }
    }
  }
}

</script>