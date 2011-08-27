require 'test_helper'

class Lms::Exam::ExamResultControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get from_student_and_exam" do
    get :from_student_and_exam
    assert_response :success
  end

  test "should get set_exam_result" do
    get :set_exam_result
    assert_response :success
  end

  test "should get delete_exam_result" do
    get :delete_exam_result
    assert_response :success
  end

  test "should get update_scoring" do
    get :update_scoring
    assert_response :success
  end

  test "should get get_latest_for_student" do
    get :get_latest_for_student
    assert_response :success
  end

end
