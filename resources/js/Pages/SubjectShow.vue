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
const reviewSubmitted = ref(false);

function createFlashcard() {
  flashcardForm.post(route("flashcards.store"), {
    onSuccess: () => {
      showFlashcardModal.value = false;
      flashcardForm.reset();
      window.location.reload();
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

function openFlashcardModal(flashcard, userRole) {
  if (userRole === "teacher") {
    return;
  }
  activeFlashcard.value = flashcard;
}

function closeFlashcardModal() {
  activeFlashcard.value = null;
  selectedAnswer.value = "";
}

const answeredFlashcards = ref(new Set());
const selectedAnswer = ref("");

async function selectAnswer(option) {
  if (userResponses.value[activeFlashcard.value.id]) {
    return;
  }

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

    answeredFlashcards.value.add(activeFlashcard.value.id);
    activeFlashcard.value = { ...activeFlashcard.value };

    const totalAnswered = Object.keys(userResponses.value).length;
    const totalFlashcards = props.subject.flashcards.length;

    if (totalAnswered === totalFlashcards) {
      const reviewKey = `subject_${props.subject.id}_review_submitted`;
      const hasSubmittedReview = localStorage.getItem(reviewKey) === "true";

      if (!hasSubmittedReview) {
        setTimeout(() => {
          showReviewModal.value = true;
          window.location.reload();
        }, 500);
      }
    }
  } catch (error) {
    console.error("Error submitting answer:", error);
  }
}

const getFlashcardClass = (flashcard) => {
  return [
    "rounded-lg p-4 relative h-32",
    flashcard.answered || answeredFlashcards.value.has(flashcard.id)
      ? "bg-blue-100"
      : "bg-gray-200",
  ];
};

const getButtonClass = (letter, correctAnswer) => {
  const baseClasses = "p-4 rounded-lg transition-colors text-center w-full";

  const existingResponse = activeFlashcard.value
    ? getUserResponse(activeFlashcard.value)
    : null;

  const newResponse = activeFlashcard.value
    ? userResponses.value[activeFlashcard.value.id]
    : null;

  const response = existingResponse || newResponse;

  if (response) {
    if (letter === correctAnswer) {
      return `${baseClasses} bg-green-500 text-white`;
    }
    if (letter === response.selected_answer && letter !== correctAnswer) {
      return `${baseClasses} bg-red-500 text-white`;
    }
  }

  return `${baseClasses} bg-gray-100 hover:bg-gray-200`;
};

async function resetResponses() {
  try {
    await axios.post(`/api/subjects/${props.subject.id}/reset-responses`);
    userResponses.value = {};
    showReviewModal.value = false;
    reviewSubmitted.value = false;
    localStorage.removeItem(`subject_${props.subject.id}_review_submitted`);
    window.location.reload();
  } catch (error) {
    console.error("Error resetting responses:", error);
  }
}

const allAnswered = computed(() => {
  const flashcards = props.subject.flashcards;

  if (!flashcards || flashcards.length === 0) {
    return false;
  }

  const answeredCount = flashcards.filter((flashcard) =>
    flashcard.responses?.some((response) => response.user_id === props.user.id)
  ).length;

  const allCardsAnswered = answeredCount === flashcards.length;

  const reviewKey = `subject_${props.subject.id}_review_submitted`;
  const hasSubmittedReview =
    localStorage.getItem(reviewKey) === "true" || reviewSubmitted.value;

  if (allCardsAnswered && !showReviewModal.value && !hasSubmittedReview) {
    showReviewModal.value = true;
  }

  return allCardsAnswered;
});

const quality = ref(5);

function submitReview() {
  axios
    .post(`/api/subjects/${props.subject.id}/review`, {
      quality: quality.value,
    })
    .then(() => {
      showReviewModal.value = false;
      reviewSubmitted.value = true;
      localStorage.setItem(
        `subject_${props.subject.id}_review_submitted`,
        "true"
      );
      window.location.reload();
    })
    .catch((error) => {
      console.error("Error submitting review:", error);
    });
}

const showEditFlashcardModal = ref(false);
const editFlashcardForm = useForm({
  question: "",
  option_a: "",
  option_b: "",
  option_c: "",
  option_d: "",
  correct_answer: "",
});

function editFlashcard(flashcard) {
  editFlashcardForm.question = flashcard.question;
  editFlashcardForm.option_a = flashcard.option_a;
  editFlashcardForm.option_b = flashcard.option_b;
  editFlashcardForm.option_c = flashcard.option_c;
  editFlashcardForm.option_d = flashcard.option_d;
  editFlashcardForm.correct_answer = flashcard.correct_answer;
  activeFlashcard.value = flashcard;
  showEditFlashcardModal.value = true;
}

function updateFlashcard() {
  editFlashcardForm.put(route("flashcards.update", activeFlashcard.value.id), {
    onSuccess: () => {
      showEditFlashcardModal.value = false;
      window.location.reload();
    },
  });
}

const isFlashcardAnswered = (flashcard) => {
  return flashcard.responses?.some(
    (response) => response.user_id === props.user.id
  );
};

const getUserResponse = (flashcard) => {
  return flashcard.responses?.find(
    (response) => response.user_id === props.user.id
  );
};

function parseQuestionWithCode(text) {
  const parts = [];
  const regex = /```([\s\S]*?)```/g;
  let lastIndex = 0;
  let match;

  while ((match = regex.exec(text)) !== null) {
    if (match.index > lastIndex) {
      parts.push({
        text: text.substring(lastIndex, match.index),
        isCode: false,
      });
    }
    parts.push({
      text: match[1],
      isCode: true,
    });
    lastIndex = match.index + match[0].length;
  }

  if (lastIndex < text.length) {
    parts.push({
      text: text.substring(lastIndex),
      isCode: false,
    });
  }

  return parts;
}

onMounted(() => {
  allAnswered.value;
  props.subject.flashcards.forEach((flashcard) => {
    const userResponse = flashcard.responses?.find(
      (r) => r.user_id === props.user.id
    );
    if (userResponse) {
      userResponses.value[flashcard.id] = {
        selected_answer: userResponse.selected_answer,
        is_correct: userResponse.is_correct,
      };
      answeredFlashcards.value.add(flashcard.id);
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
                v-if="props.userRole !== 'teacher'"
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
                :class="getFlashcardClass(flashcard)"
              >
                <div
                  class="cursor-pointer w-full h-full flex items-center justify-center"
                  @click="openFlashcardModal(flashcard, props.userRole)"
                >
                  {{
                    flashcard.question.length > 10
                      ? flashcard.question.substring(0, 10) + "..."
                      : flashcard.question
                  }}
                </div>
                <button
                  v-if="props.userRole === 'teacher'"
                  @click.stop="editFlashcard(flashcard)"
                  class="absolute bottom-2 left-1/2 transform -translate-x-1/2 text-sm bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded"
                >
                  Editar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="activeFlashcard && props.userRole !== 'teacher'"
      @click="closeFlashcardModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50"
    >
      <div
        @click.stop
        class="relative bg-white p-8 rounded-lg shadow-lg w-1/2 mx-auto"
      >
        <h3 class="font-semibold text-xl mb-4 text-left">
          <template
            v-for="(part, index) in parseQuestionWithCode(
              activeFlashcard.question
            )"
            :key="index"
          >
            <pre v-if="part.isCode" class="code-block">{{ part.text }}</pre>
            <span v-else class="whitespace-pre-wrap">{{ part.text }}</span>
          </template>
        </h3>
        <div class="grid grid-cols-1 gap-4 mt-8">
          <button
            v-for="option in options"
            :key="option.letter"
            :class="
              getButtonClass(option.letter, activeFlashcard.correct_answer)
            "
            @click="selectAnswer(option.letter)"
            :disabled="isFlashcardAnswered(activeFlashcard)"
            class="flex items-center justify-center text-center"
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
            <textarea
              id="question"
              v-model="flashcardForm.question"
              required
              rows="3"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            ></textarea>
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

    <div
      v-if="showEditFlashcardModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50"
    >
      <div class="bg-white p-8 rounded-lg shadow-lg w-1/3">
        <h3 class="font-semibold text-xl mb-4">Editar Flashcard</h3>
        <form @submit.prevent="updateFlashcard">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700"
              >Questão</label
            >
            <textarea
              v-model="editFlashcardForm.question"
              required
              rows="3"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            ></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="option in ['a', 'b', 'c', 'd']"
              :key="option"
              class="mb-4"
            >
              <label class="block text-sm font-medium text-gray-700">
                Alternativa {{ option.toUpperCase() }}
              </label>
              <input
                v-model="editFlashcardForm[`option_${option}`]"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
              />
            </div>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">
              Resposta Correta
            </label>
            <select
              v-model="editFlashcardForm.correct_answer"
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
              @click="showEditFlashcardModal = false"
              class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mr-2"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
            >
              Atualizar
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style>
.code-block {
  background-color: #f4f4f4;
  padding: 1rem;
  border-radius: 0.375rem;
  font-family: ui-monospace, monospace;
  white-space: pre-wrap;
  margin: 0.5rem 0;
  border: 1px solid #e2e8f0;
}
</style>
