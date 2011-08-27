class Core::EnrollmentController < ApplicationController
  # get "/core/Enrollment/getCurrentForStudent", :controller => "Core::Enrollment", :action => "get_current_for_student"
  def get_current_for_student
      enrollment = (Core::Enrollment).get_current_for_student(params[:student_id])
      respond_with_object(enrollment)
  end
  
  # get "/core/Enrollment/getAllForStudent", :controller => "Core::Enrollment", :action => "get_all_for_student"
  def get_all_for_student
    enrollments = (Core::Enrollment).get_all_for_student(params[:student_id])
    respond_with_object(enrollements)
  end
end
