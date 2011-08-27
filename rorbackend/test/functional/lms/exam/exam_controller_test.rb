require 'test_helper'

class Lms::Exam::ExamControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get from_course" do
    get :from_course
    assert_response :success
  end

end
