class Lms::Moodle::MoodleItemController < ApplicationController
  # get "lms/moodle/MoodleItem/get_latest_for_course", :controller => "Lms::Moodle::MoodleItem", :action => "get_latest_for_course"
  def get_latest_for_course
    moodle_items = (Lms::Moodle::MoodleItem).get_latest_for_course(params[:moodle_course_id])
    
    respond_with_object(moodle_items)
  end

end
