<template>
  <fieldset class="mb-4">
    <div>
      <legend class="text-base font-medium text-gray-900">
        {{ index + 1 }}. {{ question.question }}
      </legend>
      <p class="text-gray-500 text-sm">
        {{ question.description }}
      </p>
    </div>
    <div class="mt-3">
      <div v-if="question.type === 'select'">
        <select
          :value="modelValue"
          @change="emits('update:modelValue', $event.target.value)"
          class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          required
        >
          <option value="">Please Select</option>
          <option v-for="option in question.data.options" :key="option.uuid" :value="option.text">
            {{ option.text }}
          </option>
        </select>
      </div>
      <div v-else-if="question.type === 'radio'">
        <div
          v-for="(option, ind) of question.data.options"
          :key="option.uuid"
          class="flex items-center"
        >
          <input
            :id="option.uuid"
            :name="'question' + question.id"
            :value="option.text"
            @change="emits('update:modelValue', $event.target.value)"
            type="radio"
            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
            required
          />
          <label
            :for="option.uuid"
            class="ml-3 block text-sm font-medium text-gray-700"
          >
            {{ option.text }}
          </label>
        </div>
      </div>
      <div v-else-if="question.type === 'checkbox'">
        <div
          v-for="(option, ind) of question.data.options"
          :key="option.uuid"
          class="flex items-center"
        >
          <input
            :id="option.uuid"
            v-model="model[option.text]"
            @change="onCheckboxChange"
            type="checkbox"
            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
            required
          />
          <label
            :for="option.uuid"
            class="ml-3 block text-sm font-medium text-gray-700"
          >
            {{ option.text }}
          </label>
        </div>
      </div>
      <div v-else-if="question.type === 'text'">
        <input
          type="text"
          :value="modelValue"
          @input="emits('update:modelValue', $event.target.value)"
          class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md required:true"
        />
      </div>
      <div v-else-if="question.type === 'textarea'">
        <textarea
          :value="modelValue"
          @input="emits('update:modelValue', $event.target.value)"
          class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
        ></textarea>
      </div>
      <div v-else-if="question.type === 'int'">
        <input
          type="number"
          :value="modelValue"
          @input="emits('update:modelValue', $event.target.value)"
          class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md required:true"
          required
        />
      </div>
      <div v-else-if="question.type === 'footprint'">
        <!-- Render footprint as radio buttons, similar to 'radio' type -->
        <!-- This needs to handle options that might be strings or objects {uuid, text} -->
        <div
          v-for="(option, optIndex) in question.data.options"
          :key="typeof option === 'object' ? option.uuid : `${question.id}-${option}-${optIndex}`"
          class="flex items-center"
        >
          <input
            :id="typeof option === 'object' ? option.uuid : `${question.id}-${option}-${optIndex}`"
            :name="'question' + question.id"
            :value="typeof option === 'object' ? option.text : option"
            @change="emits('update:modelValue', $event.target.value)"
            type="radio"
            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
            :checked="modelValue === (typeof option === 'object' ? option.text : option)"
            required
          />
          <label
            :for="typeof option === 'object' ? option.uuid : `${question.id}-${option}-${optIndex}`"
            class="ml-3 block text-sm font-medium text-gray-700"
          >
            {{ typeof option === 'object' ? option.text : option }}
          </label>
        </div>
      </div>

    </div>
  </fieldset>
  <hr class="mb-4" />
</template>

<script setup>
import { ref } from "vue";
const { question, index, modelValue } = defineProps({
  question: Object,
  index: Number,
  modelValue: [String, Array],
});
const emits = defineEmits(["update:modelValue"]);

let model;
if (question.type === "checkbox") {
  model = ref({});
}

function shouldHaveOptions() {
  return ["select", "radio", "checkbox"].includes(question.type);
}

function onCheckboxChange($event) {
  const selectedOptions = [];
  for (let uuid in model.value) {
    if (model.value[uuid]) {
      selectedOptions.push(uuid);
    }
  }
  emits("update:modelValue", selectedOptions);
}
</script>

<style></style>
