<!-- This example requires Tailwind CSS v2.0+ -->
<template>


  <div v-if="loading">
    Loading
  </div>
  <div v-else>
    hi  the route id is:
    <div v-if="params">
      {{ route.params.id }}
    </div>

    {{store}}
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

store.dispatch("getAnswers");

let params = false;

if (route.params.id) {
  console.log(route.params.id);
  params = true;
}

</script>
