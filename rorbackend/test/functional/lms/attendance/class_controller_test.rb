require 'test_helper'

class Lms::Attendance::ClassControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get from_group_and_scoring_element" do
    get :from_group_and_scoring_element
    assert_response :success
  end

end
