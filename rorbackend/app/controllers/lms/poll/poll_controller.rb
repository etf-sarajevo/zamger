class Lms::Poll::PollController < ApplicationController
  # get "/lms/poll/Poll/:id", :controller => "Lms::Poll::Poll", :action => "show"
  def show
    poll = (Lms::Poll::Poll).find(params[:id])
    respond_with_object(poll)
  end
  
  # get "/lms/poll/Poll/getActiveForAllCourses", :controller => "Lms::Poll::Poll", :action => "getActiveForAllCourses"
  def get_active_for_all_courses
    polls = (Lms::Poll::Poll).joins(:academic_year).where(:academic_year => {:current => true})
    # TODO anketa_predmet???
  end
  
  # get "/lms/poll/Poll/getActiveForCourse", :controller => "Lms::Poll::Poll", :action => "get_active_for_course"
  # TODO anketa_predmet??
  def get_active_for_course
  end
  
  # get "/lms/poll/Poll/is_active_for_course", :controller => "Lms::Poll::Poll", :action => "is_active_for_course"
  # TODO anketa_predmet??
  def is_active_for_course
    
  end

end
