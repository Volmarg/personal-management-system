<template>
<div>

  <ul v-for="(data, index) in resultCheckData">
    <li>
      <b>{{ index }}</b>:
      <ok v-if="data" />
      <fail v-else />
    </li>
  </ul>

  <button class="btn btn-primary"
          ref="buttonNext"
          @click="goToPreviousStep"
  >Back</button>

  <button class="btn btn-primary"
          ref="buttonNext"
          @click="goToNextStep"
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
      stepName         : "Environment check",
      nextStepName     : "Configuration execution",
      previousStepName : "Database",
      resultCheckData  : {},
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
      axios.post(this.urls.getEnvironmentCheckResultData, dataBag).then( (response) => {
        let isSuccess        = response.data.resultCheckData;
        this.resultCheckData = response.data.resultCheckData;
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