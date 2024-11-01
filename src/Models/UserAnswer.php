<?php

namespace App\Models;

use App\Models\BaseModel;
use \PDO;

class UserAnswer extends BaseModel
{
    protected $user_id;
    protected $answers;

    public function save($user_id, $answers, $attempt_id)
    {
        $this->user_id = $user_id;
        $this->answers = $answers;

        $sql = "INSERT INTO users_answers
                SET
                    user_id=:user_id,
                    answers=:answers,
                    attempt_id=:attempt_id";        
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'user_id' => $user_id,
            'answers' => $answers,
            'attempt_id' => $attempt_id
        ]);
    
        return $statement->rowCount();
    }

    public function saveAttempt($user_id, $exam_items, $score)
    {
        $sql = "INSERT INTO exam_attempts
                SET
                    user_id=:user_id,
                    exam_items=:exam_items,
                    exam_score=:exam_score";   
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'user_id' => $user_id,
            'exam_items' => $exam_items,
            'exam_score' => $score
        ]);
        return $this->db->lastInsertId();
    }

    public function getUserAnswers() {
        // Prepare the SQL query to retrieve user answers with related data
        $sql = "
            SELECT 
                ua.answer_id,
                ua.attempt_id,
                ua.answers,
                ua.date_answered,
                ea.attempt_datetime AS attempt_date,
                u.complete_name AS examinee_name,
                u.email AS examinee_email,  
                ea.exam_items,
                ea.exam_score
            FROM 
                users_answers AS ua
            JOIN 
                users AS u ON ua.user_id = u.id
            JOIN 
                exam_attempts AS ea ON ua.attempt_id = ea.attempt_id
            ORDER BY 
                ua.date_answered DESC"; // Order by date answered, most recent first
    
        // Prepare the statement
        $stmt = $this->db->prepare($sql);
        
        // Execute the statement
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    

    public function exportData($attempt_id) {
        $sql = "
            SELECT 
                ua.answer_id,
                ua.attempt_id,
                ua.answers,
                ua.date_answered,
                ea.attempt_datetime AS attempt_date,
                u.complete_name AS examinee_name,
                u.email AS examinee_email,  -- added examinee email field
                ea.exam_items,
                ea.exam_score
            FROM 
                users_answers AS ua
            JOIN 
                users AS u ON ua.user_id = u.id
            JOIN 
                exam_attempts AS ea ON ua.attempt_id = ea.attempt_id
            WHERE 
                ea.attempt_id = :attempt_id
            ORDER BY 
                ua.date_answered DESC"; 

        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':attempt_id', $attempt_id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

}