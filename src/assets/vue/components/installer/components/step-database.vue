<template>

  <div class="form-group">
    <label class="text-normal text-dark">Database host:</label>
    <input type="text"
           class="form-control"
           placeholder="Host"
           required="required"
           v-model="databaseHost"
    >

    <label class="text-normal text-dark">Database port:</label>
    <input type="text"
           class="form-control"
           placeholder="Port"
           required="required"
           v-model="databasePort"
    >

    <label class="text-normal text-dark">Database name:</label>
    <input type="text"
           class="form-control"
           placeholder="Name"
           required="required"
           v-model="databaseName"
    >

    <label class="text-normal text-dark">Database password:</label>
    <input type="password"
           class="form-control"
           placeholder="Password"
           required="required"
           v-model="databasePassword"
    >

    <button class="btn btn-primary"
            ref="buttonNext"
            @click="goToNextStep"
    >Next</button>

  </div>

</template>

<script>
export default {
  data(){
    return {
      stepName         : "Database",
      nextStepName     : "Environment check",
      databaseHost     : "",
      databasePort     : "",
      databaseName     : "",
      databasePassword : "",
      sessionStorage: {
        stepDataKey: "stepData" + this.stepName,
      }
    }
  },
  emits: [
    "step-finished",
    "step-mounted",
  ],
  methods: {
    /**
     * @description will go to next step
     */
    goToNextStep(){
      this.saveStepDataInSession();
      this.$emit("step-finished", this.nextStepName);
    },
    /**
     * @description will save step data in session
     */
    saveStepDataInSession(){
      let stepData = {
        databaseHost     : this.databaseHost,
        databasePort     : this.databasePort,
        databaseName     : this.databaseName,
        databasePassword : this.databasePassword,
      };

      let stepDataString = JSON.stringify(stepData);
      sessionStorage.setItem(this.sessionStorage.stepDataKey, stepDataString);
    },
    /**
     * @description will load step data from session
     */
    loadStepDataFromSession(){
      let stepDataJson = sessionStorage.getItem(this.sessionStorage.stepDataKey);

      if("undefined" === stepDataJson){
        return null;
      }

      return JSON.parse(stepDataJson);
    }
  },
  mounted(){
    this.$emit("step-mounted", this.stepName);
  }
}

</script>