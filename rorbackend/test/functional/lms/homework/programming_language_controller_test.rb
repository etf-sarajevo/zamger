require 'test_helper'

class Lms::Homework::ProgrammingLanguageControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

end
