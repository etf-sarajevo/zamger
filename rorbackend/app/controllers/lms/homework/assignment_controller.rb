class Lms::Homework::AssignmentController < ApplicationController
  # get "/lms/homework/Assignment/:id", :controller => "Lms::Homework::Assignment", :action => "show"
  def show
    assignment = (Lms::Homework::Assignment).find(params[:id])
    respond_with_object(assignment)
  end
  
  # get "/lms/homework/Assignment/fromStudentHomeworkNumber", :controller => "Lms::Homework::Assignment", :action => "from_student_homework_number"
  def from_student_homework_number
    assignment = (Lms::Homework::Assignment).from_student_homework_number(params[:author_id], params[:homework_id], params[:assign_no])
    
    respond_with_object(assignement)
  end
  
  # put "/lms/homework/Assignment", :controller => "Lms::Homework::Assignment", :action => "create"
  def create
    respond_create((Lms::Homework::Assignment).new(:homework_id => params[:homework_id], :assign_no => params[:assign_no], :status => params[:status], :score => params[:score], :time => Time.now, :compile_report => params[:compile_report], :comment => params[:comment], :filename => params[:filename], :author_id => params[:author_id]).save)
  end
end
