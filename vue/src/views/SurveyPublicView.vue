<template>
  <div class="py-5 px-8">
    <div v-if="loading" class="flex justify-center">Loading...</div>
    <form @submit.prevent="submitSurvey" v-else class="container mx-auto">
      <div class="grid grid-cols-6 items-center">
        <div class="mr-4">
          <img :src="survey.image_url" alt="" />
        </div>
        <div class="col-span-5">
          <h1 class="text-3xl mb-3">{{ survey.title }}</h1>
          <p class="text-gray-500 text-sm" v-html="survey.description"></p>
        </div>
      </div>

      <div v-if="surveyFinished" class="py-8 px-6 bg-emerald-400 text-white w-[600px] mx-auto">
        <div class="text-xl mb-3 font-semibold ">Thank you for participating in this survey.</div>
        <button @click="submitAnotherResponse" type="button" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          Submit another response
        </button>
      </div>
      <div v-else>
        <hr class="my-3">
        
        <!-- GPS Location Permission Request (shown after user starts survey) -->
        <div v-if="showGPSPrompt && !gpsPermissionAsked" class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3 flex-1">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-blue-800">Location Data for Environmental Research</h3>
                <button @click="showGPSPrompt = false; gpsPermissionDismissed = true" type="button" class="text-blue-400 hover:text-blue-600">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              <div class="mt-2 text-sm text-blue-700">
                <p>This survey is part of an environmental research project. Would you like to share your location? This helps us analyze regional environmental impact patterns.</p>
              </div>
              <div class="mt-4">
                <button @click="requestGPSPermission(true)" type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2">
                  Allow Location Access
                </button>
                <button @click="requestGPSPermission(false)" type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                  Continue Without Location
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <div v-if="gpsStatus" class="mb-4 p-3 rounded-md" :class="gpsStatus.type === 'success' ? 'bg-green-50 text-green-800' : 'bg-gray-50 text-gray-700'">
          <p class="text-sm">{{ gpsStatus.message }}</p>
        </div>
        
        <div v-for="(question, ind) of survey.questions" :key="question.id">
          <QuestionViewer
            v-model="answers[question.id]"
            :question="question"
            :index="ind"
          />
        </div>

        <button
          type="submit"
          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Submit
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { useStore } from "vuex";
import QuestionViewer from "../components/viewer/QuestionViewer.vue";
const route = useRoute();
const store = useStore();

const loading = computed(() => store.state.currentSurvey.loading);
const survey = computed(() => store.state.currentSurvey.data);

const surveyFinished = ref(false);

const answers = ref({});

// GPS-related state
const gpsPermissionAsked = ref(false);
const gpsPermissionDismissed = ref(false);
const gpsCoordinates = ref(null);
const gpsStatus = ref(null);
const showGPSPrompt = ref(false);

// GPS UI configuration
const GPS_PROMPT_DELAY_MS = 2000; // Delay before showing GPS prompt after user interaction

store.dispatch("getSurveyBySlug", route.params.slug);

/**
 * Watch for user interaction with survey to show GPS prompt
 * This creates a less intrusive experience by waiting until
 * the user has started filling out the survey
 */
watch(answers, (newAnswers) => {
  if (Object.keys(newAnswers).length > 0 && !gpsPermissionAsked.value && !gpsPermissionDismissed.value && !showGPSPrompt.value) {
    // Delay showing the prompt to let user focus on the question first
    setTimeout(() => {
      showGPSPrompt.value = true;
    }, GPS_PROMPT_DELAY_MS);
  }
}, { deep: true });

/**
 * Request GPS permission from user
 * @param {boolean} allow - Whether user allows GPS access
 */
function requestGPSPermission(allow) {
  gpsPermissionAsked.value = true;
  showGPSPrompt.value = false;
  
  if (!allow) {
    gpsStatus.value = {
      type: 'info',
      message: 'Continuing without location data. Your responses will still be recorded.'
    };
    // Store preference to avoid asking again
    gpsPermissionDismissed.value = true;
    return;
  }
  
  // Check if geolocation is supported
  if (!navigator.geolocation) {
    gpsStatus.value = {
      type: 'error',
      message: 'Geolocation is not supported by your browser.'
    };
    return;
  }
  
  // Request current position
  gpsStatus.value = {
    type: 'info',
    message: 'Requesting location...'
  };
  
  navigator.geolocation.getCurrentPosition(
    (position) => {
      gpsCoordinates.value = {
        latitude: position.coords.latitude,
        longitude: position.coords.longitude
      };
      gpsStatus.value = {
        type: 'success',
        message: '✓ Location captured successfully for environmental research.'
      };
    },
    (error) => {
      let errorMessage = 'Unable to retrieve location. ';
      switch(error.code) {
        case error.PERMISSION_DENIED:
          errorMessage += 'Location permission was denied.';
          break;
        case error.POSITION_UNAVAILABLE:
          errorMessage += 'Location information is unavailable.';
          break;
        case error.TIMEOUT:
          errorMessage += 'Location request timed out.';
          break;
        default:
          errorMessage += 'An unknown error occurred.';
      }
      gpsStatus.value = {
        type: 'error',
        message: errorMessage
      };
      console.error('GPS error:', error);
    },
    {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 0
    }
  );
}

function submitSurvey() {
  console.log(JSON.stringify(answers.value, undefined, 2));
  
  // Prepare submission data with GPS coordinates if available
  const submissionData = {
    surveyId: survey.value.id,
    answers: answers.value,
  };
  
  // Add GPS coordinates if they were captured
  if (gpsCoordinates.value) {
    submissionData.latitude = gpsCoordinates.value.latitude;
    submissionData.longitude = gpsCoordinates.value.longitude;
  }
  
  store
    .dispatch("saveSurveyAnswer", submissionData)
    .then((response) => {
      if (response.status === 201) {
        surveyFinished.value = true;
      }
    });
}

function submitAnotherResponse() {
  answers.value = {};
  surveyFinished.value = false;
  // Preserve GPS permission state for subsequent submissions
  // User preference is remembered within the session
}
</script>

<style></style>
