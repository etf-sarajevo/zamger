class Lms::Moodle::MoodleIdController < ApplicationController
  # get "/lms/moodle/MoodleId/getMoodleId", :controller => "Lms::Moodle::MoodleId", :action => "get_moodle_id"
  def get_moodle_id
    moodle_id = (Lms::Moodle::MoodleId).get_moodle_id(params[:course_unit_id], params[:academic_year_id])
    respond_with_object(moodle_id)
  end
  
end
