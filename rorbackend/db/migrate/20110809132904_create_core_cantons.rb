class CreateCoreCantons < ActiveRecord::Migration
  def change
    create_table :core_cantons do |t|
      t.string :name, :limit => 50
      t.string :short_name, :limit => 5

      # t.timestamps
    end
  end
end
