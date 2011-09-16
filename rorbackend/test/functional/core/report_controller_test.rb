require 'test_helper'

class Core::ReportControllerTest < ActionController::TestCase
  test "should get course_unit" do
    get :course_unit
    assert_response :success
  end

end
