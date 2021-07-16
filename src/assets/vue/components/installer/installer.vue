<template>
<div class="installer-wrapper">
  <loader ref="loader"/>

  <h3 class="text-center mt-3 text-dark">Installer</h3>
  <h4 class="text-center mt-1 text-dark"> {{ stepName }} </h4>
  <hr/>

  <step-database
      v-show="isMounted && isThisStepActive($refs.stepDatabase.stepName)"
      @step-finished="onStepDatabaseFinished"
      ref="stepDatabase"
  />

  <step-environment-check-component
      v-show="isMounted && isThisStepActive($refs.stepEnvironmentCheck.stepName)"
      @step-finished="onStepEnvironmentCheckFinished"
      @step-cancelled="onStepEnvironmentCheckCancelled"
      @step-failed="onStepFailed"
      :perform-check="performEnvironmentCheck"
      ref="stepEnvironmentCheck"
  />

  <step-configuration-execution-component
      v-show="isMounted && isThisStepActive($refs.stepConfigurationExecution.stepName)"
      @step-cancelled="onStepConfigurationExecutionCancelled"
      @step-failed="onStepFailed"
      @step-finished="onStepConfigurationExecutionFinished"
      :perform-configuration="performConfigurationExecution"
      ref="stepConfigurationExecution"
  />

  <p v-if="isInstallationCompleted">
    <i>Installation has been completed - proceed to the login page.</i>
  </p>

  <div v-if="isStepFailed">
    <h3 class="text-center mt-3 text-dark">Installation log</h3>
    <br/>
    <i class="d-block text-center">Installation could not been finished - something went wrong!</i>

    <hr/>
    <section class="scroll-y log-content">
      <span v-html="logContent"></span>
    </section>
    <hr/>
  </div>

</div>
</template>

<script>

import StepDatabaseComponent               from "./components/step-database.vue";
import StepEnvironmentCheckComponent       from "./components/step-envorionment-check.vue";
import StepConfigurationExecutionComponent from "./components/step-configuration-execution.vue";
import LoaderComponent                     from "./components/loader.vue";
import axios                               from "axios";

export default {
  data(){
    return {
      isStepFailed                  : false,
      isInstallationCompleted       : false,
      logContent                    : "",
      performEnvironmentCheck       : false,
      performConfigurationExecution : false,
      stepName                      : "test",
      currentActiveStepName         : "Database",
      isMounted                     : false,
      stepsNames : {
        environmentCheck  : "Environment check",
        database          : "Database",
        databaseModeCheck : "Database mode check",
      },
      urls: {
        getEnvironmentCheckResultData: "/installer.php?GET_LOG_FILE_CONTENT",
      }
    };
  },
  components: {
    "loader"                                 : LoaderComponent,
    "step-database"                          : StepDatabaseComponent,
    "step-environment-check-component"       : StepEnvironmentCheckComponent,
    "step-configuration-execution-component" : StepConfigurationExecutionComponent,
  },
  methods: {
    /**
     * @description will check if given step is active
     * @return {Boolean}
     */
    isThisStepActive(elementStepName){
      if(elementStepName === this.currentActiveStepName){
        this.stepName = elementStepName
        return true;
      }
      return false;
    },
    /**
     * @description handles the case when user clicks on "next" in the database step
     * @param nextStepName {String}
     */
    onStepDatabaseFinished(nextStepName){
      this.stepSwitch(nextStepName);
      this.performEnvironmentCheck = true;
    },
    /**
     * @description handles the case when the environment check step is completed
     * @param nextStepName {String}
     */
    onStepEnvironmentCheckFinished(nextStepName){
      this.stepSwitch(nextStepName);
      this.performConfigurationExecution = true;
    },
    /**
     * @description handles the case when the environment check step is cancelled
     * @param previousStepName {String}
     */
    onStepEnvironmentCheckCancelled(previousStepName){
      this.stepSwitch(previousStepName);
      this.performEnvironmentCheck = false;
      this.isStepFailed = false;
    },
    /**
     * @description handles the case when step has failed
     */
    onStepFailed(){
      this.isStepFailed = true;
      this.getLogFileContent();
    },
    /**
     * @description handles the case when the configuration step is cancelled
     * @param previousStepName {String}
     */
    onStepConfigurationExecutionCancelled(previousStepName){
      this.stepSwitch(previousStepName);
      this.performEnvironmentCheck       = false;
      this.performConfigurationExecution = false;
      this.isStepFailed                  = false;
    },
    /**
     * @description handles the case when the configuration step is finished
     */
    onStepConfigurationExecutionFinished(){
      this.isInstallationCompleted = true;
    },
    /**
     * @description will change current step
     * @param stepName
     */
    stepSwitch(stepName){
      this.stepName              = stepName;
      this.currentActiveStepName = stepName;
      this.isStepFailed          = false; // if back then need to reset anyway
    },
    getLogFileContent(){
      axios.get(this.urls.getEnvironmentCheckResultData).then( (result) => {
        this.logContent = result.data.logFileContent;
      })
    }
  },
  mounted(){
    this.isMounted = true;
  }
}
</script>


<style scoped>
.installer-wrapper {
  position: relative;
}

.log-content {
  height: 250px;
}
</style>