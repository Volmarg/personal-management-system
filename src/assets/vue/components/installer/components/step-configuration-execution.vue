<template>
<div>

  <h5>Configuring environment - please wait</h5>
  <!-- todo: add small loader for waiting (add some general component)-->

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

</div>
</template>

<script>

import axios from "axios";

import FailMarkComponent from "./fail-mark.vue";
import OkMarkComponent   from "./ok-mark.vue";

export default {
  data(){
    return {
      stepName         : "Configuration execution",
      nextStepName     : "todo",
      previousStepName : "Environment check",
      resultCheckData  : {},
      urls: {
        configureAndPrepareSystem: "/installer.php?STEP_CONFIGURATION_EXECUTION",
      }
    }
  },
  props: {
    performConfiguration : {
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
     * @description will return environment check result data
     */
    configureAndPrepareSystem(){
      let dataBag = this.$parent.$refs.stepDatabase.loadStepDataFromSession();
      axios.post(this.urls.configureAndPrepareSystem, dataBag).then( (response) => {
        this.resultCheckData = response.data.resultCheckData;
      })
    },
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
    }
  },
  mounted(){
    this.$emit("step-mounted", this.stepName);
  },
  watch: {
    performConfiguration(newValue){
      if(newValue){
        this.resultCheckData = {};
        this.configureAndPrepareSystem();
      }
    }
  }
}

</script>