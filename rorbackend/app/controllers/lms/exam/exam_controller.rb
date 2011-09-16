class Lms::Exam::ExamController < ApplicationController
  caches_action :from_course, :cache_path => Proc.new { |c| c.params }
  # get "/lms/exam/Exam/:id", :controller => "Lms::Exam::Exam", :action => "show"
  def show
    exam = (Lms::Exam::Exam).from_id(params[:id])
    respond_with_object(exam)
  end
  
  # get "/lms/exam/Exam/fromCourse", :controller => "Lms::Exam::Exam", :action => "from_course"
  def from_course
    exams = (Lms::Exam::Exam).from_course(params[:course_unit_id], params[:academic_year_id], params[:order])
    respond_to do |format|
      format.json { render :json => exams.to_json(:include => :scoring_element) }
    end
  end
end
