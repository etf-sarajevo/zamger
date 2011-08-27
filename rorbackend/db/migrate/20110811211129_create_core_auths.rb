class CreateCoreAuths < ActiveRecord::Migration
  def change
    create_table :core_auths do |t|
      t.string :login, :limit => 50
      t.string :password, :limit => 20
      t.boolean :admin
      t.string :external_id, :limit => 50
      t.boolean :active

      # t.timestamps
    end
  end
end
