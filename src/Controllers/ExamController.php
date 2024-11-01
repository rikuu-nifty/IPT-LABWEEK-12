<?php 

namespace App\Controllers;

use App\Models\Question;
use App\Models\UserAnswer;
use App\Models\User;

class ExamController extends BaseController
{   
    public function registrationForm()
    {
        $this->initializeSession();

        return $this->render('registration-form');
    }

    public function register()
    {   
        $this->initializeSession();
        $data = $_POST;

        $userObj = new User();
        $user_id = $userObj->save($data);

        session_destroy();

        // Save the registration to database
        $_SESSION['user_id'] = $user_id; // Replace this literal value with the actual user ID from new registration
        $_SESSION['complete_name'] = $data['complete_name'];
        $_SESSION['email'] = $data['email'];

        header("Location: /login");
    }

    public function exam()
    {   
        $this->initializeSession();
        if (isset($_SESSION['is_logged_in'])) {
            $item_number = 1;

            // If request is coming from the form, save the inputs to the session
            if (isset($_POST['item_number']) && isset($_POST['answer'])) {
                array_push($_SESSION['answers'], $_POST['answer']);
                $_SESSION['item_number'] = $_POST['item_number'] + 1;
            }

            if (!isset($_SESSION['item_number'])) {
                // Initialize session variables
                $_SESSION['item_number'] = $item_number;
                $_SESSION['answers'] = [false];
            } else {
                $item_number = $_SESSION['item_number'];
            }

            $data = $_POST;
            $questionObj = new Question();
            $question = $questionObj->getQuestion($item_number);

            // if there are no more questions, save the answers
            if (is_null($question) || !$question) {
                $user_id = $_SESSION['user_id'];
                $json_answers = json_encode($_SESSION['answers']);

                error_log('FINISHED EXAM, SAVING ANSWERS');
                error_log('USER ID = ' . $user_id);
                error_log('ANSWERS = ' . $json_answers);

                $userAnswerObj = new UserAnswer();
                $score = $questionObj->computeScore($_SESSION['answers']);
                $items = $questionObj->getTotalQuestions();
                $attempt_id = $userAnswerObj->saveAttempt($user_id, $items, $score);
                $userAnswerObj->save(
                    $user_id,
                    $json_answers,
                    $attempt_id
                );

                header("Location: /result");
                exit;
            }

            $question['choices'] = json_decode($question['choices']);

            return $this->render('exam', $question);
        }
        header("Location: /login");
    }

    public function result()
    {
        $this->initializeSession();
        if(isset($_SESSION['is_logged_in'])) {
            $data = $_SESSION;
            $questionObj = new Question();
            $data['questions'] = $questionObj->getAllQuestions();
            $answers = $_SESSION['answers'];
            foreach ($data['questions'] as &$question) {
                $question['choices'] = json_decode($question['choices']);
                $question['user_answer'] = $answers[$question['item_number']];
            }
            $data['total_score'] = $questionObj->computeScore($_SESSION['answers']);
            $data['question_items'] = $questionObj->getTotalQuestions();

            return $this->render('result', $data);
        }
        header("Location: /login");
    }
}
