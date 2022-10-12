<!-- This example requires Tailwind CSS v2.0+ -->
<template>
  <PageComponent>
    <template v-slot:header>
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900"> Respuestas {{ survey.title }}</h1>
      </div>
    </template>

    <div v-if="loading" class="flex justify-center">Loading...</div>

    <div>
      <div class="flex flex-col">
        <div class="overflow-x-auto">
          <div class="p-1.5 w-full inline-block align-middle">
            <div class="overflow-hidden border rounded-lg">
              <h2 v-if="!questions"> NO se encontraron preguntas relacionadas a esta encuesta</h2>

              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>

                    <th
                      scope="col"
                      class="px-6 py-3 text-xs font-bold text-left text-gray-500 uppercase"
                      v-if="questions"
                      v-for="question in questions">
                        {{question.question}}
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" v-if="answers">
                  <tr v-for="(item, index) in answers">
                    <td
                      class="px-6 py-4 text-sm font-medium text-gray-800 whitespace-nowrap"
                      v-for="(answer) in answers[index]"
                    >
                      {{ answer }}
                    </td>
                  </tr>
                </tbody>
              </table>

              <div v-if="!answers"> No se encontraron respuestas </div>
            </div>
          </div>
        </div>
      </div>

    </div>

  </PageComponent>
</template>

<script setup>
import { useRoute } from "vue-router";
import { computed } from "vue";
import store from "../store";
import PageComponent from "../components/PageComponent.vue";

const route = useRoute();
// const store = useStore();

const loading = computed(() => store.state.answers.loading);
const data = computed(() => store.state.answers.data);

const answers = computed(() => store.state.answers.data.answers);
const questions = computed(() => store.state.answers.data.questions);
const survey = computed(() => store.state.answers.data.survey);

let params = false;

if (route.params.id) {
  store.dispatch("getAnswer",route.params.id);
  params = true;
}
if (!route.params.id) {
  store.dispatch("getAnswers");
}

</script>
