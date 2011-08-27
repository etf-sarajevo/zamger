require 'test_helper'

class Core::ProgrammeControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

end
