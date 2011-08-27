require 'test_helper'

class Lms::Homework::DiffControllerTest < ActionController::TestCase
  test "should get create" do
    get :create
    assert_response :success
  end

end
