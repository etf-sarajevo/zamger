require 'test_helper'

class Lms::Poll::PollResultControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get from_hash" do
    get :from_hash
    assert_response :success
  end

  test "should get from_student_and_poll" do
    get :from_student_and_poll
    assert_response :success
  end

  test "should get create" do
    get :create
    assert_response :success
  end

  test "should get update" do
    get :update
    assert_response :success
  end

end
