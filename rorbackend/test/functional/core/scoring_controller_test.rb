require 'test_helper'

class Core::ScoringControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get get_scoring_elements" do
    get :get_scoring_elements
    assert_response :success
  end

end
