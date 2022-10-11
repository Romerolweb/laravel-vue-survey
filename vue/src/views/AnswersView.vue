<!-- This example requires Tailwind CSS v2.0+ -->
<template>


  <div v-if="loading">
    Loading
  </div>
  <div v-else>
    <table>
      <thead> 

      </thead>
    </table>

<!--    hi  the route id is:-->
<!--    <div v-if="params">-->
<!--      {{ route.params.id }}-->
<!--    </div>-->
   <pre>
<!--     {{data}}-->
   </pre>
    <pre>
     {{answers}}
   </pre>
  </div>


  <br>

</template>

<script setup>
import { useRoute, useRouter } from "vue-router";
import { computed } from "vue";
import store from "../store";
// import { useStore } from "vuex";
//
// const router = useRouter();

const route = useRoute();
// const store = useStore();

const loading = computed(() => store.state.answers.loading);
const data = computed(() => store.state.answers.data);

const answers = computed(() => store.state.answers);


let params = false;

if (route.params.id) {
  store.dispatch("getAnswer",route.params.id);
  params = true;
}
if (!route.params.id) {
  store.dispatch("getAnswers");
}

</script>
