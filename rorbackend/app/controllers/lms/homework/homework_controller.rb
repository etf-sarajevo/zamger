class Lms::Homework::HomeworkController < ApplicationController
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
  end
  
  # post "/lms/homework/Homework/updateScoreForStudent", :controller => "Lms::Homework::Homework", :action => "update_score_for_student"
  def update_score_for_student
    respond_save((Lms::Homework::Homework).update_score_for_student(student_id, course_unit_id, academic_year_id))
  end
  
end
