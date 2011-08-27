require 'test_helper'

class Lms::Poll::PollQuestionControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get get_all_for_poll" do
    get :get_all_for_poll
    assert_response :success
  end

  test "should get set_answer_rank" do
    get :set_answer_rank
    assert_response :success
  end

  test "should get set_answer_essay" do
    get :set_answer_essay
    assert_response :success
  end

  test "should get set_answer_choice" do
    get :set_answer_choice
    assert_response :success
  end

end
