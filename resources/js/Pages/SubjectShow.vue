<script setup>
import { ref, computed, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";

const { props } = usePage();
const isDueForReview = props.isDueForReview;
const showFlashcardModal = ref(false);
const showReviewModal = ref(false);
const userResponses = ref({});
const flashcardForm = useForm({
  question: "",
  option_a: "",
  option_b: "",
  option_c: "",
  option_d: "",
  correct_answer: "",
  subject_id: props.subject.id,
});

function createFlashcard() {
  flashcardForm.post(route("flashcards.store"), {
    onSuccess: () => {
      showFlashcardModal.value = false;
      flashcardForm.reset();
    },
  });
}

const activeFlashcard = ref(null);

const options = computed(() => {
  if (activeFlashcard.value) {
    return [
      { letter: "a", text: activeFlashcard.value.option_a },
      { letter: "b", text: activeFlashcard.value.option_b },
      { letter: "c", text: activeFlashcard.value.option_c },
      { letter: "d", text: activeFlashcard.value.option_d },
    ];
  }
  return [];
});

function openFlashcardModal(flashcard) {
  activeFlashcard.value = flashcard;
}

function closeFlashcardModal() {
  activeFlashcard.value = null;
}

const selectedAnswer = ref("");

async function selectAnswer(option) {
  if (selectedAnswer.value) {
    return;
  }
  selectedAnswer.value = option;

  try {
    const response = await axios.post(
      `/api/flashcards/${activeFlashcard.value.id}/respond`,
      {
        selected_answer: option,
      }
    );
    const isCorrect = response.data.is_correct;

    userResponses.value[activeFlashcard.value.id] = {
      selected_answer: option,
      is_correct: isCorrect,
    };
  } catch (error) {
    console.error("Error submitting answer:", error);
  }
}

function getButtonClass(option, answer) {
  let baseClasses = "bg-gray-100 rounded-lg p-4 text-gray-700 font-bold";
  const response = userResponses.value[activeFlashcard.value.id];
  if (response) {
    if (option === answer) {
      return `${baseClasses} bg-green-500 text-white`;
    } else if (option === response.selected_answer) {
      return `${baseClasses} bg-red-500 text-white`;
    }
  }
  return baseClasses;
}

async function resetResponses() {
  try {
    await axios.post(`/api/subjects/${props.subject.id}/reset-responses`);
    userResponses.value = {};
    showReviewModal.value = false;
    alert("Respostas resetadas para uma nova revisão.");
  } catch (error) {
    console.error("Error resetting responses:", error);
  }
}

const allAnswered = computed(() => {
  let response = props.subject.flashcards.every(
    (flashcard) => userResponses.value[flashcard.id]
  );
  if (response && !showReviewModal.value) {
    showReviewModal.value = true;
  }
});

const quality = ref(5);

function submitReview() {
  axios
    .post(`/api/subjects/${props.subject.id}/review`, {
      quality: quality.value,
    })
    .then(() => {
      props.subject.completed = true;
      showReviewModal.value = false;
      window.location.reload();
    })
    .catch((error) => {
      console.error("Error submitting review:", error);
    });
}

onMounted(() => {
  props.subject.flashcards.forEach((flashcard) => {
    if (flashcard.user_response) {
      userResponses.value[flashcard.id] = flashcard.user_response;
    }
  });
});
</script>

<template>
  <AuthenticatedLayout :user="props.user">
    <Head title="Detalhes do Tópico" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="flex items-center justify-between p-6 text-gray-900">
            <div class="flex-1 min-w-0 flex items-center justify-between">
              <h2 class="text-2xl font-semibold truncate">
                {{ props.subject.name }}
              </h2>
              <button
                @click="resetResponses"
                class="py-2 px-4 bg-blue-500 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none"
              >
                Reiniciar Revisão
              </button>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
              <button
                v-if="props.userRole === 'teacher'"
                @click="showFlashcardModal = true"
                class="py-2 px-4 bg-blue-500 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none"
              >
                Adicionar Flashcard
              </button>
            </div>
          </div>
          <div class="p-6">
            <div
              class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 justify-center p-6"
            >
              <div
                v-for="flashcard in props.subject.flashcards"
                :key="flashcard.id"
                class="bg-gray-200 rounded-lg p-4 flex justify-center items-center h-32 cursor-pointer"
                @click="openFlashcardModal(flashcard)"
              >
                {{ flashcard.question }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="activeFlashcard"
      @click="closeFlashcardModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50"
    >
      <div
        @click.stop
        class="relative bg-white p-8 rounded-lg shadow-lg w-1/2 mx-auto"
      >
        <h3 class="font-semibold text-xl mb-4 text-center">
          {{ activeFlashcard.question }}
        </h3>
        <div class="grid grid-cols-1 gap-4 mt-8">
          <button
            v-for="option in options"
            :key="option.letter"
            :class="
              getButtonClass(option.letter, activeFlashcard.correct_answer)
            "
            @click="selectAnswer(option.letter)"
          >
            {{ option.text }}
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="showFlashcardModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center"
    >
      <div class="bg-white p-8 rounded-lg shadow-lg w-1/3">
        <h3 class="font-semibold text-xl mb-4">Adicionar Flashcard</h3>
        <form @submit.prevent="createFlashcard">
          <div class="mb-4">
            <label
              for="question"
              class="block text-sm font-medium text-gray-700"
              >Questão</label
            >
            <input
              type="text"
              id="question"
              v-model="flashcardForm.question"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="option in ['a', 'b', 'c', 'd']"
              :key="option"
              class="mb-4"
            >
              <label
                :for="`option_${option}`"
                class="block text-sm font-medium text-gray-700"
                >Alternativa {{ option.toUpperCase() }}</label
              >
              <input
                :id="`option_${option}`"
                v-model="flashcardForm[`option_${option}`]"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
              />
            </div>
          </div>

          <div class="mb-4">
            <label
              for="correct_answer"
              class="block text-sm font-medium text-gray-700"
              >Resposta Correta</label
            >
            <select
              id="correct_answer"
              v-model="flashcardForm.correct_answer"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            >
              <option value="">Selecione a resposta correta</option>
              <option value="a">A</option>
              <option value="b">B</option>
              <option value="c">C</option>
              <option value="d">D</option>
            </select>
          </div>

          <div class="flex items-center justify-end">
            <button
              type="button"
              @click="showFlashcardModal = false"
              class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mr-2"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
            >
              Adicionar
            </button>
          </div>
        </form>
      </div>
    </div>

    <div
      v-if="showReviewModal && allAnswered"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50"
    >
      <div class="bg-white p-8 rounded-lg shadow-lg w-1/3">
        <h3 class="font-semibold text-xl mb-4">Revisão Completa</h3>
        <p>Avalie seu desempenho geral (0-5):</p>
        <input
          type="number"
          v-model="quality"
          min="0"
          max="5"
          class="mt-2 mb-4 w-full border rounded-md p-2"
        />
        <button
          @click="submitReview"
          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
        >
          Enviar Avaliação
        </button>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
