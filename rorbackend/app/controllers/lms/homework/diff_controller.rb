class Lms::Homework::DiffController < ApplicationController
  # put "/lms/homework/Diff", :controller => "Lms::Homework::Diff", :action => "create"
  def create
    respond_create((Lms::Homework::Diff).new(:assignment_id => params[:assignment_id], :diff => params[:diff]).save)
  end

end
