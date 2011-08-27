require 'test_helper'

class Common::Pm::MessageControllerTest < ActionController::TestCase
  test "should get show" do
    get :show
    assert_response :success
  end

  test "should get for_person" do
    get :for_person
    assert_response :success
  end

  test "should get send" do
    get :send
    assert_response :success
  end

  test "should get get_latest_for_person" do
    get :get_latest_for_person
    assert_response :success
  end

  test "should get get_outbox_for_person" do
    get :get_outbox_for_person
    assert_response :success
  end

end
