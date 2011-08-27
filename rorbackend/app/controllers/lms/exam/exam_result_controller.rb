class Lms::Exam::ExamResultController < ApplicationController
  # get "/lms/exam/ExamResult/:id", :controller => "Lms::Exam::ExamResult", :action => "show"
  def show
    exam_result = (Lms::Exam::ExamResult).find(params[:id])
    respond_with_object(exam_result)
  end
  
  # get "/lms/exam/ExamResult/fromStudentAndExam", :controller => "Lms::Exam::ExamResult", :action => "from_student_and_exam"
  def from_student_and_exam
    exam_result = (Lms::Exam::ExamResult).from_student_and_exam(params[:student_id], params[:exam_id])
    
    respond_with_object(exam_result)
  end
  
  # post "/lms/exam/ExamResult/:id/setExamResult", :controller => "Lms::Exam::ExamResult", :action => "set_exam_result"
  def set_exam_result
    respond_save((Lms::Exam::ExamResult).set_exam_result(params[:id], params[:result]))
  end

  # delete "/lms/exam/ExamResult/:id/deleteExamResult", :controller => "Lms::Exam::ExamResult", :action => "delete_exam_result"
  def delete_exam_result
    respond_delete(Lms::Exam::ExamResult).delete(params[:id]))
  end
  
  # Not availaible
  def update_scoring
    respond_save((Lms::Exam::ExamResult).update_scoring(params[:exam_id], params[:student_id]))
  end
  
  # get "/lms/exam/ExamResult/getLatestForStudent", :controller => "Lms::Exam::ExamResult", :action => "get_latest_for_student"
  def get_latest_for_student
    exam_results = (Lms::Exam::ExamResult).get_latest_for_student(params[:student_id], params[:limit])
    
    respond_with_object(exam_results)
  end

end
