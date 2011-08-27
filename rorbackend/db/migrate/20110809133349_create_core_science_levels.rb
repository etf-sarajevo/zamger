class CreateCoreScienceLevels < ActiveRecord::Migration
  def change
    create_table :core_science_levels do |t|
      t.string :name, :limit => 50
      t.string :title, :limit => 15

      # t.timestamps
    end
  end
end
