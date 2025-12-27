<!-- This example requires Tailwind CSS v2.0+ -->
<template>
  <PageComponent title="Respuestas">
    <div v-if="loading" class="flex justify-center">Loading...</div>

      <div class="flex justify-between items-center">
        <h1 v-if="survey" class="text-3xl font-bold text-gray-900">{{ survey.title }}</h1>
      </div>

    <div>
      <div class="flex flex-col">
        <div class="overflow-x-auto">
          <div class="p-1.5 w-full inline-block align-middle">
            <div class="overflow-hidden border rounded-lg">
              <h3 v-if="!answers || answers.length === 0"> NO se encontraron respuestas relacionadas a esta encuesta.</h3>

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
                <tbody class="divide-y divide-gray-200" v-if="answers && questions && Object.keys(questions).length > 0">
                  <tr v-for="(submissionAnswers, submissionId) in answers" :key="submissionId">
                    <td
                      class="px-6 py-4 text-sm font-medium text-gray-800 whitespace-nowrap"
                      v-for="question in questions"
                      :key="question.id"
                    >
                      {{ submissionAnswers[question.id] !== undefined ? submissionAnswers[question.id] : '-' }}
                    </td>
                  </tr>
                </tbody>
                <tbody v-else-if="!loading && (!answers || Object.keys(answers).length === 0)">
                  <tr>
                    <td :colspan="questions ? Object.keys(questions).length : 1" class="text-center py-4 text-gray-500">
                      No answers found for this survey yet.
                    </td>
                  </tr>
                </tbody>
              </table>

              <div v-if="!loading && answers && Object.keys(answers).length === 0 && questions && Object.keys(questions).length > 0" class="text-center py-4 text-gray-500">
                 No answers found for this survey yet.
              </div>
               <div v-if="!loading && (!questions || Object.keys(questions).length === 0) && survey" class="text-center py-4 text-gray-500">
                 This survey currently has no questions defined for displaying answers.
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

  </PageComponent>
</template>

<script setup>
import { useRoute } from "vue-router";
import { computed, watch } from "vue";
import store from "../store";
import PageComponent from "../components/PageComponent.vue";

const route = useRoute();

const loading = computed(() => store.state.answers.loading);

// Ensure initial state for answers, questions, survey are objects to prevent undefined access
const answers = computed(() => store.state.answers.data && store.state.answers.data.answers ? store.state.answers.data.answers : {});
const questions = computed(() => store.state.answers.data && store.state.answers.data.questions ? store.state.answers.data.questions : {});
const survey = computed(() => store.state.answers.data && store.state.answers.data.survey ? store.state.answers.data.survey : {});


// Fetch data when the component is created or route.params.id changes
if (route.params.id) {
  store.dispatch("getAnswer", route.params.id);
} else {
  // console.warn("Displaying all answers for all surveys is not fully supported by this view's design.");
  // For now, let's clear any existing answer data if no ID is present or redirect.
  // Or, display a message. For now, we'll rely on the survey title not appearing.
  // store.dispatch("getAnswers"); // This was problematic, disabling for now.
  // A better approach might be to redirect if no ID, or show a selection list.
}

// Watch for route changes if the component is reused for different survey answer views
watch(() => route.params.id, (newId, oldId) => {
  if (newId) {
    store.dispatch("getAnswer", newId);
  }
});

</script>
