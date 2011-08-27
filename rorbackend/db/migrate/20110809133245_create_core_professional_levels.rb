class CreateCoreProfessionalLevels < ActiveRecord::Migration
  def change
    create_table :core_professional_levels do |t|
      t.string :name, :limit => 100
      t.string :title, :limit => 15

      # t.timestamps
    end
  end
end
