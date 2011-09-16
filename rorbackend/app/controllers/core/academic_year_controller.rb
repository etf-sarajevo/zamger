class Core::AcademicYearController < ApplicationController
  caches_action :show, :cache_path => Proc.new { |c| c.params }
  # get "/core/AcademicYear/:id", :controller => "Core::AcademicYear", :action => "show"
  def show
    academic_year = (Core::AcademicYear).find(params[:id])
    respond_with_object(academic_year)
  end
 
  # get "/core/AcademicYear/getCurrent", :controller => "Core::AcademicYear", :action => "get_current"
  def get_current
    current_year = (Core::AcademicYear).current_year
    respond_with_object(current_year)
  end

  # post "/core/AcademicYear/:id/setAsCurrent", :controller => "Core::AcademicYear", :action => "set_as_current"
  def set_as_current
    respond_save((Core::AcademicYear).set_as_current(params[:id]))
 end

end