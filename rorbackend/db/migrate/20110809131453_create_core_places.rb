class CreateCorePlaces < ActiveRecord::Migration
  def change
    create_table :core_places do |t|
      t.string :name, :limit => 40
      t.integer :municipality_id
      t.integer :country_id
      
      # t.timestamps
    end
  end
end
