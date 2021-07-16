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

  <a class="btn btn-primary"
     href="/login"
     v-if="isLoginButtonVisible"
  >Login</a>

</div>
</template>

<script>

import axios from "axios";

import FailMarkComponent from "./fail-mark.vue";
import OkMarkComponent   from "./ok-mark.vue";

export default {
  data(){
    return {
      loaderText       : `
                            Configuring system, please wait...
                            <br/>
                            <span class="d-flex justify-content-center"> Approximately (1-3 min)... </span>
                        `,
      isSuccessTextVisible : false,
      isFailTextVisible    : false,
      isBackButtonVisible  : true,
      isLoginButtonVisible : false,
      stepName             : "Configuration execution",
      nextStepName         : "todo",
      previousStepName     : "Environment check",
      resultCheckData      : {},
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
    "step-failed",
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

      this.$parent.$refs.loader.setText(this.loaderText);
      this.$parent.$refs.loader.show();

      axios.post(this.urls.configureAndPrepareSystem, dataBag).then( (response) => {
        this.$parent.$refs.loader.hide();
        this.$parent.$refs.loader.clearText();

        this.resultCheckData = response.data.resultCheckData;

        if(response.data.success){
          this.$parent.$refs.stepDatabase.clearStepDataFromSession();

          this.isBackButtonVisible  = false
          this.isLoginButtonVisible = true;
          this.isSuccessTextVisible = true;
          this.isFailTextVisible    = false;

          this.$emit("step-finished");
        }else{
          this.isBackButtonVisible  = true
          this.isLoginButtonVisible = false;
          this.isSuccessTextVisible = false;
          this.isFailTextVisible    = true;

          this.$emit("step-failed");
        }

      })
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