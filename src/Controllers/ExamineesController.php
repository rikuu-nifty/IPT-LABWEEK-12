<?php 

namespace App\Controllers;

use App\Models\UserAnswer;
use App\Models\User;
use Fpdf\Fpdf;

class ExamineesController extends BaseController
{
    public function index() {
        $this->initializeSession();
        if (isset($_SESSION['is_logged_in'])) {
            $userObj = new User();
            $examinees = $userObj->getAllUsers();
    
            $userAnsObj = new UserAnswer();
            $data = $userAnsObj->getUserAnswers();
    
            $combinedData = [
                'examinees' => $examinees,
                'data' => $data, // Assuming this is also an array
            ];
    
            return $this->render('examinees', $combinedData);
        }
        header("Location: /login");
    }

    public function exportToPDF($attempt_id)
{
    // Initialize UserAnswer object and fetch data
    $obj = new UserAnswer();
    $data = $obj->exportData($attempt_id);

    // Create an instance of FPDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Set document title with larger, bold font
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetTextColor(0, 0, 0); // Change title color to a bright orange
    $pdf->Cell(190, 15, 'Examinee Attempt Details', 0, 1, 'C');
    $pdf->Ln(10);

    // Section: Examinee Information
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(255, 255, 255); // White color for header text
    $pdf->SetFillColor(0, 102, 204); // Changed to a lighter blue for headers
    $pdf->Cell(190, 10, 'Examinee Information', 0, 1, 'C', true);
    $pdf->Ln(5);

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0); // Black for body text
    $pdf->Cell(50, 10, 'Name:', 0, 0);
    $pdf->Cell(100, 10, $data['examinee_name'], 0, 1);
    $pdf->Cell(50, 10, 'Email:', 0, 0);
    $pdf->Cell(100, 10, $data['examinee_email'], 0, 1);
    $pdf->Cell(50, 10, 'Attempt Date:', 0, 0);
    $pdf->Cell(100, 10, $data['attempt_date'], 0, 1);
    $pdf->Cell(50, 10, 'Exam Items:', 0, 0);
    $pdf->Cell(100, 10, $data['exam_items'], 0, 1);
    $pdf->Cell(50, 10, 'Exam Score:', 0, 0);
    $pdf->Cell(100, 10, $data['exam_score'], 0, 1);
    $pdf->Ln(10);

    // Section: Answers Submitted
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFillColor(0, 102, 204); // Keeping the same lighter blue for answers section
    $pdf->Cell(190, 10, 'Answers Submitted', 0, 1, 'C', true);
    $pdf->Ln(5);

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->MultiCell(0, 10, 'Answers: ' . $data['answers']);
    $pdf->Cell(50, 10, 'Date Answered:', 0, 0);
    $pdf->Cell(100, 10, $data['date_answered'], 0, 1);
    $pdf->Ln(10);

    // Output the PDF as a download
    $pdf->Output('D', 'examinee_attempt_' . $attempt_id . '.pdf');
}


}