class Lms::Homework::HomeworkController < ApplicationController
  caches_action :from_course_scoring_element, :cache_path => Proc.new { |c| c.params }
  caches_action :from_course, :cache_path => Proc.new { |c| c.params }
  caches_action :get_score_from_course_unit, :cache_path => Proc.new { |c| c.params }
  # get "/lms/homework/Homework/:id", :controller => "Lms::Homework::Homework", :action => "show"
  def show
    homework = (Lms::Homework::Homework).from_id(params[:id])
    respond_with_object(homework)
  end
  
  # get "/lms/homework/Homework/getLatestForStudent", :controller => "Lms::Homework::Homework", :action => "get_latest_for_student"
  # TODO
  def get_latest_for_student
    homeworks = (Lms::Homework::Homework).get_latest_for_student(params[:student_id], params[:limit])
    respond_with_object(homeworks)
  end
  
  # get "/lms/homework/Homework/getReviewedForStudent", :controller => "Lms::Homework::Homework", :action => "get_reviewed_for_student"
  def get_reviewed_for_student
    homeworks = (Lms::Homework::Homework).get_reviewed_for_student(params[:student_id], params[:limit])
    respond_with_object(homeworks)
  end
  
  # get "/lms/homework/Homework/fromCourse", :controller => "Lms::Homework::Homework", :action => "from_course"
  def from_course
    homeworks = (Lms::Homework::Homework).from_course(params[:course_unit_id], params[:academic_year_id])
    respond_with_object(homeworks)
  end
  
  # post "/lms/homework/Homework/updateScoreForStudent", :controller => "Lms::Homework::Homework", :action => "update_score_for_student"
  def update_score_for_student
    respond_save((Lms::Homework::Homework).update_score_for_student(params[:student_id], params[:course_unit_id], params[:academic_year_id]))
  end
  
  # get "/lms/homework/Homework/fromCourseScoringElement", :controller => "Lms::Homework::Homework", :action => "from_course_scoring_element"
  def from_course_scoring_element
    scoring_elements = (Lms::Homework::Homework).from_course_scoring_element(params[:course_unit_id], params[:academic_year_id])
    
    respond_with_object(scoring_elements)
  end
  
  # get "/lms/homework/Homework/getScoreFromCourseUnit", :controller => "Lms::Homework::Homework", :action => "get_score_from_course_unit"
  def get_score_from_course_unit
    score = (Core::ScoringElementScore).get_score_from_course_unit(params[:student_id], params[:course_unit_id], params[:academic_year_id], params[:homework_id])
    respond_with_object(score)
  end
  
end
