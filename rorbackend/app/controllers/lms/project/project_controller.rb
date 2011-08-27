class Lms::Project::ProjectController < ApplicationController
  # get "/Lms/Project/Project/:id", :controller => "Lms::Project::Project", :action => "show"
  def show
    project = (Lms::Project::Project).find(params[:id])
    respond_with_object(project)
  end
  
  # get "/Lms/Project/Project/get_all_for_course", :controller => "Lms::Project::Project", :action => "get_all_for_course"
  def get_all_for_course
    pojects = (Lms::Project::Project).where(:course_unit_id => params[:course_unit_id])
    respond_with_object(projects)
  end
  
  # get "/Lms/Project/Project/from_member_and_course", :controller => "Lms::Project::Project", :action => "from_member_and_course"
  def from_member_and_course
    project = (Lms::Project::Project).from_member_and_course(params[:course_unit_id], params[:academic_year_id], params[:student_id])
    respond_with_object(project)
  end
  
  # get "/Lms/Project/Project/:id/is_member", :controller => "Lms::Project::Project", :action => "is_member"
  def is_member
    num_results = (Lms::Project::Project).is_member(params[:id], params[:student_id])
    respond_with_object(member)
  end
  
  # get "/Lms/Project/Project/:id/get_members", :controller => "Lms::Project::Project", :action => "get_members"
  def get_members
    members = (Lms::Project::Project).get_members(params[:id])
    respond_with_object(members)
  end
  
  # put "/Lms/Project/Project/:id/add_member", :controller => "Lms::Project::Project", :action => "add_member"
  def add_member
    respond_create((Lms::Project::Project).add_member(params[:id], params[:student_id]))
  end
  
  # delete "/Lms/Project/Project/:id/delete_member", :controller => "Lms::Project::Project", :action => "delete_member"
  def delete_member
    respond_delete((Lms::Project::ProjectStudent).delete_member(params[:id], params[:student_id]))
  end

end
