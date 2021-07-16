<template>

  <div class="form-group">
    <label class="text-normal text-dark">Database host<span class="text-danger">*</span> :</label>
    <input type="text"
           class="form-control"
           placeholder="Host"
           required="required"
           v-model="databaseHost"
    >

    <label class="text-normal text-dark">Database port<span class="text-danger">*</span>:</label>
    <input type="text"
           class="form-control"
           placeholder="Port"
           required="required"
           v-model="databasePort"
    >

    <label class="text-normal text-dark">Database name<span class="text-danger">*</span>:</label>
    <input type="text"
           class="form-control"
           placeholder="Name"
           required="required"
           v-model="databaseName"
    >

    <label class="text-normal text-dark">Database login<span class="text-danger">*</span>:</label>
    <input type="text"
           class="form-control"
           placeholder="Login"
           required="required"
           v-model="databaseLogin"
    >

    <label class="text-normal text-dark">Database password<span class="text-danger">*</span>:</label>
    <input type="password"
           class="form-control"
           placeholder="Password"
           required="required"
           v-model="databasePassword"
    >

    <span class="text-danger">*</span> <i>These fields are required</i>

    <br/>

    <button class="btn btn-primary"
            ref="buttonNext"
            @click="goToNextStep"
            v-if="isDatabaseDataSet()"
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
      databaseLogin    : "",
      databasePassword : "",
    }
  },
  emits: [
    "step-finished",
    "step-mounted",
  ],
  computed: {
    /**
     * @description will return session storage key under which the step data is being stored
     */
    sessionStorageStepDataKey(){
      return "stepData" + this.stepName;
    }
  },
  methods: {
    /**
     * @description will check if all required fields are set
     * @return {boolean}
     */
    isDatabaseDataSet(){
      return (
              this.databaseHost
          &&  this.databasePort
          &&  this.databaseName
          &&  this.databaseLogin
          &&  this.databasePassword
      )
    },
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
        databaseLogin    : this.databaseLogin,
      };

      let stepDataString = JSON.stringify(stepData);
      sessionStorage.setItem(this.sessionStorageStepDataKey, stepDataString);
    },
    /**
     * @description will load step data from session
     */
    loadStepDataFromSession(){
      let stepDataJson = sessionStorage.getItem(this.sessionStorageStepDataKey);

      if("undefined" === stepDataJson){
        return null;
      }

      return JSON.parse(stepDataJson);
    },
    /**
     * @description will remove step data from session
     */
    clearStepDataFromSession(){
      sessionStorage.removeItem(this.sessionStorageStepDataKey);
    }
  },
  mounted(){
    let stepData = this.loadStepDataFromSession();
    if(null !== stepData){
      this.databaseHost     = stepData.databaseHost;
      this.databasePort     = stepData.databasePort;
      this.databaseName     = stepData.databaseName;
      this.databasePassword = stepData.databasePassword;
      this.databaseLogin    = stepData.databaseLogin;
    }

    this.$emit("step-mounted", this.stepName);
  }
}

</script>