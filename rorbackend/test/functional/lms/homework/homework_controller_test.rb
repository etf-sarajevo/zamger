require 'test_helper'

class Lms::Homework::HomeworkControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get get_latest_for_student" do
    get :get_latest_for_student
    assert_response :success
  end

  test "should get get_reviewed_for_student" do
    get :get_reviewed_for_student
    assert_response :success
  end

  test "should get from_course" do
    get :from_course
    assert_response :success
  end

  test "should get update_score_for_student" do
    get :update_score_for_student
    assert_response :success
  end

end
