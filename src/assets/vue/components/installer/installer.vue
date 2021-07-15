<template>

  <h3 class="text-center mt-3">Installer</h3>
  <h4 class="text-center mt-1"> {{ stepName }} </h4>
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
      :perform-check="performEnvironmentCheck"
      ref="stepEnvironmentCheck"
    />

  <step-configuration-execution-component
      v-show="isMounted && isThisStepActive($refs.stepConfigurationExecution.stepName)"
      @step-cancelled="onStepConfigurationExecutionCancelled"
      :perform-configuration="performConfigurationExecution"
      ref="stepConfigurationExecution"
  />

</template>

<script>

import StepDatabaseComponent               from "./components/step-database.vue";
import StepEnvironmentCheckComponent       from "./components/step-envorionment-check.vue";
import StepConfigurationExecutionComponent from "./components/step-configuration-execution.vue";

export default {
  data(){
    return {
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
    };
  },
  components: {
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
    },
    /**
     * @description handles the case when the environment check step is cancelled
     * @param previousStepName {String}
     */
    onStepConfigurationExecutionCancelled(previousStepName){
      this.stepSwitch(previousStepName);
      this.performEnvironmentCheck       = false;
      this.performConfigurationExecution = false;
    },
    /**
     * @description will change current step
     * @param stepName
     */
    stepSwitch(stepName){
      this.stepName              = stepName;
      this.currentActiveStepName = stepName;
    }
  },
  mounted(){
    this.isMounted = true;
  }
}
</script>