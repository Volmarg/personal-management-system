<template>
<div>

  <button class="btn btn-primary"
          ref="buttonNext"
          @click="goToPreviousStep"
  >Back</button>

</div>
</template>

<script>

export default {
  data(){
    return {
      stepName         : "Configuration execution",
      nextStepName     : "todo",
      previousStepName : "Environment check",
    }
  },
  emits: [
    "step-finished",
    "step-cancelled",
    "step-mounted",
  ],
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
    }
  },
  mounted(){
    this.$emit("step-mounted", this.stepName);
  }
}

</script>