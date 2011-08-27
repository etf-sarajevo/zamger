require 'test_helper'

class Lms::Poll::PollAnswerControllerTest < ActionController::TestCase
  test "should get for_question" do
    get :for_question
    assert_response :success
  end

end
